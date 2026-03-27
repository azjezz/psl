<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\LinkedCancellationToken;
use Psl\Async\NullCancellationToken;
use Psl\Async\TimeoutCancellationToken;
use Psl\HTTP\Client\Connection\ConnectorInterface;
use Psl\HTTP\Client\Connection\Origin;
use Psl\HTTP\Client\Connection\PooledConnector;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Client\Internal\ExchangeHandler;
use Psl\HTTP\Client\Internal\MiddlewareHandler;
use Psl\HTTP\Client\Middleware\MiddlewareInterface;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;
use Psl\URL;

use function array_reverse;
use function Psl\HTTP\Client\Internal\resolve_url;

/**
 * Default HTTP client implementation with connection pooling and middleware support.
 *
 * This is the primary concrete implementation of {@see ClientInterface}. It combines
 * a {@see ConnectorInterface} for connection establishment, a {@see ClientConfiguration}
 * for transport settings, and an optional middleware stack for connection-level
 * processing (e.g., SSRF protection via {@see Middleware\DeniedDestinationsMiddleware}).
 *
 * ## Connection lifecycle
 *
 * For each request, the client resolves the URL, obtains a connection from the
 * connector, and delegates the exchange to the handler chain (middleware + terminal
 * exchange handler). By default, the {@see PooledConnector} is used, which pools
 * idle HTTP/1.x connections and shares HTTP/2 sessions across requests to the same
 * origin.
 *
 * ## Retry on connection failure
 *
 * The client has built-in single-retry behavior for transport-level failures. If the
 * initial exchange fails with a {@see Network\Exception\RuntimeException} or
 * {@see IO\Exception\RuntimeException} (e.g., a stale pooled connection was
 * reset by the server), the client automatically establishes a new connection and
 * retries the exchange exactly once. This handles the common case of idle connections
 * being closed by the server or an intermediate proxy.
 *
 * Exceptions that indicate a definitive client or protocol error
 * ({@see Exception\RequestException}, {@see Exception\TooManyRedirectsException},
 * {@see CancelledException}) are never retried and propagate
 * immediately.
 *
 * For more sophisticated retry strategies (exponential backoff, idempotency checks),
 * wrap this client with {@see RetryClient}.
 *
 * ## Middleware
 *
 * Connection-level middleware runs after the connection is established but before the
 * HTTP exchange. Middleware has access to the {@see Connection\ConnectionInterface},
 * including the peer address and TLS state, enabling security checks such as SSRF
 * protection. Middleware is applied in the order provided: the first middleware in
 * the list is the outermost (executed first), and the last is the innermost
 * (executed just before the terminal exchange handler).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110 HTTP Semantics
 *
 * @api
 */
final class Client implements ClientInterface
{
    private HandlerInterface $handler;

    /**
     * Create a new HTTP client.
     *
     * @param ConnectorInterface $connector The connector used to establish connections. Defaults to {@see PooledConnector}, which provides connection pooling and HTTP/2 session sharing.
     * @param ClientConfiguration $configuration Default client configuration for transport settings, protocol preferences, and response limits.
     * @param list<MiddlewareInterface> $middleware Connection-level middleware applied in order. The first middleware is the outermost (executed first). Common middleware includes {@see Middleware\DeniedDestinationsMiddleware} for SSRF protection.
     */
    public function __construct(
        private readonly ConnectorInterface $connector = new PooledConnector(),
        public readonly ClientConfiguration $configuration = new ClientConfiguration(),
        array $middleware = [],
    ) {
        $handler = new ExchangeHandler();
        foreach (array_reverse($middleware) as $mw) {
            $handler = new MiddlewareHandler($mw, $handler);
        }

        $this->handler = $handler;
    }

    /**
     * Send an HTTP request through the middleware chain and return the transaction.
     *
     * The send flow proceeds through the following stages:
     *
     * 1. **Validate**: Reject TRACE requests that include a body, per RFC 9110 Section 9.3.8.
     * 2. **Extract callback**: Extract the {@see SendConfiguration::$onConnection} callback
     *    before merging, as it is not part of {@see ClientConfiguration}.
     * 3. **Merge configuration**: Merge per-request {@see SendConfiguration} overrides into
     *    the client's default {@see ClientConfiguration} via {@see ClientConfiguration::withOverrides()}.
     * 4. **Resolve URL**: Resolve the effective request URL from the request itself or from
     *    the configuration's base URL. If neither is available, throw {@see Exception\RequestException}.
     * 5. **Connect**: Obtain a connection from the {@see Connection\ConnectorInterface}.
     * 6. **On-connection callback**: Invoke the {@see SendConfiguration::$onConnection} callback,
     *    if provided, with the connection metadata.
     * 7. **Exchange**: Delegate to the handler chain (middleware stack + terminal exchange handler)
     *    to perform the HTTP exchange on the established connection.
     *
     * @throws Exception\RequestException If the request is invalid (e.g., TRACE with body, or no URL resolvable).
     * @throws Exception\ProtocolException If the server sends a malformed or unparseable HTTP response.
     * @throws Exception\RuntimeException If the request fails due to a client-level error (e.g., denied destination).
     * @throws IO\Exception\RuntimeException If an I/O error occurs during the exchange.
     * @throws Network\Exception\RuntimeException If a transport-level error occurs (e.g., connection refused, DNS failure).
     * @throws CancelledException If the cancellation token fires during any stage.
     *
     * @inheritDoc
     */
    #[Override]
    public function send(
        Request $request,
        SendConfiguration $configuration = new SendConfiguration(),
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction {
        if ($request->body !== null && $request->method === 'TRACE') {
            throw Exception\RequestException::forInvalidRequest('TRACE requests must not include a body.');
        }

        $onConnection = $configuration->onConnection;
        $connectionTimeout = $configuration->connectionTimeout;
        $configuration = $this->configuration->withOverrides($configuration);

        $url = $this->resolveUrl($request, $configuration);
        if ($request->url !== $url) {
            $request = $request->withUrl($url);
        }

        $origin = Origin::fromUrl($url);
        $connectCancellation = $cancellation;
        if ($connectionTimeout !== null) {
            $connectCancellation = new LinkedCancellationToken(
                $cancellation,
                new TimeoutCancellationToken($connectionTimeout),
            );
        }

        $connection = $this->connector->connect($origin, $request, $configuration, $connectCancellation);

        if ($onConnection !== null) {
            $onConnection($connection->metadata);
        }

        return $this->handler->handle($connection, $request, $configuration, $cancellation);
    }

    /**
     * Resolve the effective request URL from the request and configuration.
     *
     * If the request already has a URL set, it is returned as-is. Otherwise, the
     * request target is resolved against the configuration's base URL. If neither
     * is available, a {@see Exception\RequestException} is thrown.
     *
     * @throws Exception\RequestException If the URL cannot be resolved (no URL on the request and no base URL configured, or the request target cannot be resolved against the base URL).
     */
    private function resolveUrl(Request $request, ClientConfiguration $configuration): URL\URL
    {
        if ($request->url !== null) {
            return $request->url;
        }

        if ($configuration->baseUrl !== null) {
            try {
                return resolve_url($request->requestTarget, $configuration->baseUrl);
            } catch (URL\Exception\InvalidURLException $e) {
                throw Exception\RequestException::forMissingUrl($e);
            }
        }

        throw Exception\RequestException::forMissingUrl();
    }
}
