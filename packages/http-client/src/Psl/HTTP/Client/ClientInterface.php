<?php

declare(strict_types=1);

namespace Psl\HTTP\Client;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;

/**
 * Primary entry point for sending HTTP requests.
 *
 * A client sends an HTTP request and returns a {@see Transaction} containing the
 * final response, any informational (1xx) responses received before it, and any
 * HTTP/2 server-pushed exchanges. The client is responsible for URL resolution,
 * connection establishment, optional middleware execution, and the HTTP exchange
 * itself.
 *
 * Implementations may add cross-cutting behavior such as automatic redirect
 * following ({@see RedirectClient}), retry logic ({@see RetryClient}), or
 * connection-level middleware (e.g., {@see Middleware\DeniedDestinationsMiddleware}
 * for SSRF protection). These behaviors are typically composed by wrapping one
 * client implementation inside another (decorator pattern).
 *
 * ## Behavioral contracts
 *
 * Implementations MUST:
 *
 * - Reject TRACE requests that include a body by throwing
 *   {@see Exception\RequestException}, per RFC 9110 Section 9.3.8.
 * - Merge per-request {@see SendConfiguration} overrides with the client's
 *   default {@see ClientConfiguration} via {@see ClientConfiguration::withOverrides()},
 *   so that null fields in the send configuration inherit from the client default.
 * - Resolve relative request targets against the configuration's base URL
 *   when the request has no URL set directly.
 * - Invoke the {@see SendConfiguration::$onConnection} callback, if provided,
 *   after acquiring a connection and before performing the HTTP exchange.
 *
 * ## Configuration
 *
 * Each client holds a default {@see ClientConfiguration} that governs transport
 * settings such as protocol version preferences, TLS configuration, response size
 * limits, and HTTP/2 session parameters. Callers may override these defaults on a
 * per-request basis by passing a {@see SendConfiguration} to {@see send()}.
 *
 * ## Error handling
 *
 * Exceptions fall into several categories:
 *
 * - {@see Exception\RequestException}: the request itself is invalid (e.g., missing URL, body on TRACE).
 * - {@see Exception\RuntimeException}: client-level error, including network policy violations
 *   (e.g., denied destination from {@see Middleware\DeniedDestinationsMiddleware}).
 * - {@see Exception\ProtocolException}: the server sent a malformed or unparseable response.
 * - {@see Exception\TooManyRedirectsException}: redirect limit exceeded (when using {@see RedirectClient}).
 * - {@see Network\Exception\RuntimeException}: transport-level failure (connection refused, DNS failure, timeout).
 * - {@see IO\Exception\RuntimeException}: I/O error during the exchange (read/write failure on the socket).
 * - {@see Async\Exception\CancelledException}: the cancellation token fired during any stage.
 *
 * Transport-level exceptions ({@see Network\Exception\RuntimeException},
 * {@see IO\Exception\RuntimeException}) are allowed to propagate unwrapped
 * from the underlying transport packages.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110 HTTP Semantics
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-9.3.8 TRACE
 *
 * @api
 */
interface ClientInterface
{
    /**
     * Send an HTTP request and return the transaction.
     *
     * Implementations MUST resolve the request URL (using the request's URL or
     * the configuration's base URL), establish a connection, execute any registered
     * middleware, and perform the HTTP exchange. The returned {@see Transaction}
     * MUST contain the final response, any preceding informational (1xx) responses,
     * and any HTTP/2 server-pushed exchanges.
     *
     * Implementations MUST merge the per-request {@see SendConfiguration} overrides
     * with the client's default {@see ClientConfiguration} before establishing the
     * connection. When no configuration is provided, the client's default
     * configuration is used unchanged.
     *
     * The cancellation token MUST be respected throughout all stages: URL resolution,
     * connection establishment, middleware execution, and the HTTP exchange.
     *
     * @param Request $request The HTTP request to send. MUST have either a URL set directly or a request target resolvable against the configuration's base URL.
     * @param SendConfiguration $configuration Per-request configuration overrides. Null fields inherit from the client's default {@see ClientConfiguration}.
     * @param CancellationTokenInterface $cancellation Token to cancel the operation at any stage. Implementations MUST check this token during URL resolution, connection establishment, middleware execution, and the HTTP exchange.
     *
     * @throws Exception\RequestException If the request is invalid (e.g., no URL and no base URL configured, or a TRACE request includes a body).
     * @throws Exception\ProtocolException If the server sends a malformed or unparseable HTTP response.
     * @throws Exception\TooManyRedirectsException If the redirect limit is exceeded (when wrapped by {@see RedirectClient}).
     * @throws Exception\RuntimeException If the request fails due to a client-level error (e.g., connection denied by network policy middleware).
     * @throws IO\Exception\RuntimeException If an I/O error occurs during the exchange (e.g., read/write failure on the underlying socket).
     * @throws Network\Exception\RuntimeException If a transport-level error occurs (e.g., connection refused, DNS resolution failure, connect timeout).
     * @throws Async\Exception\CancelledException If the cancellation token fires during any stage of the request lifecycle.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
     */
    public function send(
        Request $request,
        SendConfiguration $configuration = new SendConfiguration(),
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction;
}
