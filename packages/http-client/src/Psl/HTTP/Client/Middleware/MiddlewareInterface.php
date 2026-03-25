<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Middleware;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
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
 * is performed. It has full access to the {@see ConnectionInterface}, including the
 * peer address and TLS state, enabling connection-aware processing such as security
 * checks, logging, metrics collection, and request/response transformation.
 *
 * ## Middleware chain
 *
 * Middleware participates in a chain-of-responsibility pattern. Each middleware
 * receives the connection, request, configuration, and a {@see HandlerInterface}
 * representing the rest of the chain. The middleware can:
 *
 * - **Inspect the connection**: Check the peer address for SSRF protection
 *   ({@see DeniedDestinationsMiddleware}), log TLS negotiation details, or
 *   collect connection metrics.
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
 * Middleware is registered via the {@see \Psl\HTTP\Client\Client} constructor.
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
     * Implementations should call `$handler->handle(...)` to delegate to the next
     * handler in the chain, unless they intend to short-circuit the exchange.
     *
     * @param ConnectionInterface $connection The established connection with peer address and TLS state available for inspection.
     * @param Request $request The HTTP request to process. Middleware may modify this before delegating.
     * @param ClientConfiguration $configuration Client configuration governing transport behavior.
     * @param HandlerInterface $handler The next handler in the chain. Call {@see HandlerInterface::handle()} to continue the exchange.
     * @param CancellationTokenInterface $cancellation Token to cancel the operation at any point.
     *
     * @throws RuntimeException If the request fails.
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
