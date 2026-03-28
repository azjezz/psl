<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Middleware;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Client\Handler\HandlerInterface;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;

/**
 * Connection-level middleware for intercepting HTTP exchanges.
 *
 * Middleware runs after the connection is established but before the HTTP exchange
 * is performed. It has full access to the {@see ConnectionInterface}, including
 * connection metadata such as the resolved peer address and TLS negotiation state.
 * This enables connection-aware processing such as security checks, logging,
 * metrics collection, and request/response transformation.
 *
 * Unlike request-level middleware found in many HTTP libraries, this middleware
 * operates at the connection level. Implementations receive the established
 * connection itself, not just the request. This design enables security policies
 * that depend on the resolved network address (e.g., SSRF protection via
 * {@see DeniedDestinationsMiddleware}), TLS certificate inspection, and
 * connection-level telemetry.
 *
 * ## Implementation contract
 *
 * Implementations MUST do one of the following:
 *
 * 1. Call {@see HandlerInterface::handle()} to delegate to the next handler in
 *    the chain and return the resulting {@see Transaction} (optionally modified).
 *    The handler is responsible for calling {@see ConnectionInterface::finalize()}
 *    on the transaction before returning it.
 * 2. Return a {@see Transaction} directly to short-circuit the chain, bypassing
 *    all remaining middleware and the handler. When short-circuiting,
 *    implementations MUST return the result of {@see ConnectionInterface::finalize()},
 *    not the raw transaction. Without finalization, the underlying connection is
 *    never released back to the pool (for HTTP/1.x keep-alive connections), causing
 *    a connection leak. The correct pattern is:
 *
 * ```php
 * return $connection->finalize(new Transaction([], null, $cachedResponse));
 * ```
 *
 * When delegating, implementations MUST pass the cancellation token through to
 * the handler.
 *
 * ## Middleware chain
 *
 * Middleware participates in a chain-of-responsibility pattern. Each middleware
 * receives the connection, request, configuration, and a {@see HandlerInterface}
 * representing the rest of the chain. The middleware can:
 *
 * - **Inspect the connection**: Check the peer address for SSRF protection
 *   ({@see DeniedDestinationsMiddleware}), log TLS negotiation details, or
 *   collect connection metrics via {@see ConnectionInterface::$metadata}.
 * - **Modify the request**: Add, remove, or transform headers before the exchange.
 * - **Delegate to the next handler**: Call `$handler->handle(...)` to continue
 *   the chain and perform the exchange.
 * - **Short-circuit the chain**: Return a {@see Transaction} directly without
 *   calling the handler (e.g., for cached responses or request blocking).
 * - **Post-process the transaction**: Inspect or transform the response after
 *   the exchange completes.
 *
 * ## Registration
 *
 * Middleware is registered via the {@see Client\Client} constructor.
 * The middleware list is applied in order: the first middleware is the outermost
 * (executed first), and the last is the innermost (executed just before the
 * terminal exchange handler).
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
 *
 * @api
 */
interface MiddlewareInterface
{
    /**
     * Process the HTTP exchange with access to the established connection.
     *
     * Implementations MUST either call {@see HandlerInterface::handle()} to delegate
     * to the next handler in the chain, or return a {@see Transaction} directly to
     * short-circuit the exchange. When delegating, the implementation MAY modify the
     * request before passing it to the handler and MAY transform the returned
     * transaction before returning it.
     *
     * The connection is fully established when this method is called, meaning DNS
     * resolution, TCP connection, and TLS handshake (if applicable) have already
     * completed. Implementations can inspect the connection's metadata via
     * {@see ConnectionInterface::$metadata} to access the resolved peer address
     * (for security policy enforcement such as SSRF protection) and TLS state
     * (for certificate pinning, protocol logging, or audit trails).
     *
     * To short-circuit the chain without performing an HTTP exchange, return the
     * result of {@see ConnectionInterface::finalize()} directly. This is useful
     * for returning cached responses or blocking requests that fail a security
     * policy check. When short-circuiting, the handler and all inner middleware
     * are bypassed entirely:
     *
     * ```php
     * return $connection->finalize(new Transaction([], null, $response));
     * ```
     *
     * Implementations MUST forward the cancellation token to the handler when
     * delegating. Failure to do so will make the exchange non-cancellable from
     * the caller's perspective.
     *
     * @param ConnectionInterface $connection The established connection with peer address and TLS state available via {@see ConnectionInterface::$metadata}.
     * @param Request $request The HTTP request to process. Middleware may modify this before delegating.
     * @param ClientConfiguration $configuration Client configuration governing transport behavior.
     * @param HandlerInterface $handler The next handler in the chain. Call {@see HandlerInterface::handle()} to continue the exchange.
     * @param CancellationTokenInterface $cancellation Token to cancel the operation at any point.
     *
     * @throws RuntimeException If the middleware rejects the request or the downstream exchange fails.
     * @throws ProtocolException If the server sends a malformed or unparseable HTTP response.
     * @throws IO\Exception\RuntimeException If an I/O error occurs during the exchange.
     * @throws Network\Exception\RuntimeException If a transport-level error occurs during the exchange.
     * @throws Async\Exception\CancelledException If the cancellation token fires during the exchange.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
     */
    public function process(
        ConnectionInterface $connection,
        Request $request,
        ClientConfiguration $configuration,
        HandlerInterface $handler,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction;
}
