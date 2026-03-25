<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Handler;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Connection\ConnectionInterface;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Transaction;
use Psl\IO;
use Psl\Network;

/**
 * Performs an HTTP exchange on an established connection.
 *
 * A handler is the final step in the connection-level middleware chain. It receives
 * an already-established {@see ConnectionInterface}, a request, and a configuration,
 * and performs the HTTP exchange to produce a {@see Transaction}.
 *
 * ## Handler chain pattern
 *
 * The HTTP client uses a chain-of-responsibility pattern for connection-level
 * processing. Each {@see MiddlewareInterface} wraps a handler, forming a chain
 * where middleware intercepts the exchange and delegates to the next handler:
 *
 * ```
 * Client -> Middleware A -> Middleware B -> Handler (exchange)
 * ```
 *
 * The terminal handler at the bottom of the chain delegates to
 * {@see ConnectionInterface::exchange()} to perform the actual HTTP exchange.
 * Middleware can inspect the connection (peer address, TLS state), modify the
 * request, short-circuit the chain by returning a response directly, or perform
 * post-processing on the transaction.
 *
 * Implementations of this interface are typically internal. Public API users
 * interact with the handler chain indirectly through {@see MiddlewareInterface}
 * and the {@see \Psl\HTTP\Client\Client} constructor.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
 *
 * @api
 */
interface HandlerInterface
{
    /**
     * Execute the HTTP exchange on the given connection.
     *
     * Sends the request over the established connection and returns a
     * {@see Transaction} containing the final response, any informational (1xx)
     * responses, and any HTTP/2 server-pushed exchanges.
     *
     * @param ConnectionInterface $connection The established connection to exchange on.
     * @param Request $request The HTTP request to send.
     * @param ClientConfiguration $configuration Client configuration governing transport behavior.
     * @param CancellationTokenInterface $cancellation Token to cancel the exchange at any point.
     *
     * @throws RuntimeException If the request fails.
     * @throws ProtocolException If the server sends a malformed or unparseable HTTP response.
     * @throws IO\Exception\RuntimeException If an I/O error occurs during the exchange.
     * @throws Network\Exception\RuntimeException If a transport-level error occurs during the exchange.
     * @throws Async\Exception\CancelledException If the cancellation token fires during the exchange.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
     */
    public function handle(
        ConnectionInterface $connection,
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction;
}
