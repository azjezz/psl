<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Handler;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client;
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
 * Implementations MUST propagate the cancellation token to any underlying
 * exchange or I/O call. If the token fires, the connection may be left
 * in an indeterminate state and should not be reused.
 *
 * Implementations of this interface are typically internal. Public API users
 * interact with the handler chain indirectly through {@see MiddlewareInterface}
 * and the {@see Client\Client} constructor.
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
     * Implementations MUST call {@see ConnectionInterface::finalize()} on the
     * transaction produced by {@see ConnectionInterface::exchange()} and return
     * the finalized result. This ensures the connection is properly released
     * back to the pool when the response body is consumed.
     *
     * The request body, if present, is streamed from the {@see Request::$body}
     * read handle during the exchange. The response body in the returned
     * transaction may also be streamed; callers should consume the response
     * body before the connection is reused or released.
     *
     * @param ConnectionInterface $connection The established connection to exchange on. The connection's metadata (peer address, TLS state) is available for inspection.
     * @param Request $request The HTTP request to send. The request URL determines the Host header and request target.
     * @param ClientConfiguration $configuration Client configuration governing transport behavior such as maximum response header size and response body size limits.
     * @param CancellationTokenInterface $cancellation Token to cancel the exchange. Cancellation may occur at any point during sending or receiving.
     *
     * @throws RuntimeException If a transport-level or client error occurs during the exchange, such as a connection reset or unexpected disconnection.
     * @throws ProtocolException If the server sends a malformed or unparseable HTTP response that violates the HTTP protocol.
     * @throws IO\Exception\RuntimeException If an I/O error occurs while reading from or writing to the connection.
     * @throws Network\Exception\RuntimeException If a network-level transport error occurs, such as a socket failure or DNS resolution error.
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
