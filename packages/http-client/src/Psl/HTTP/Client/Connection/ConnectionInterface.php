<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Connection;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Client\Exception\RuntimeException;
use Psl\HTTP\Message\Request;
use Psl\HTTP\Message\Response;
use Psl\HTTP\Message\Transaction;

/**
 * A protocol-aware HTTP exchange channel.
 *
 * Represents a single exchange-ready connection over which HTTP requests can
 * be sent and responses received. The underlying protocol, HTTP/1.1 (one
 * request per TCP connection), HTTP/2 (one stream on a multiplexed connection),
 * or HTTP/3 (one QUIC stream), is fully encapsulated by the implementation.
 * Callers interact only through this interface regardless of protocol version.
 *
 * Implementations are not expected to be safe for concurrent use. For HTTP/2,
 * where multiple streams share a single TCP connection, each stream should be
 * represented as a separate {@see ConnectionInterface} instance backed by the
 * same underlying multiplexed connection.
 *
 * Connections are typically obtained from a {@see ConnectorInterface} and may
 * be managed by a pooling layer such as {@see PooledConnector}. Callers should
 * not assume a connection is reusable after an exchange completes; connection
 * lifecycle is managed by the connector and pool.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.3 Connections and Transport
 * @link https://datatracker.ietf.org/doc/html/rfc9113 HTTP/2
 * @link https://datatracker.ietf.org/doc/html/rfc9114 HTTP/3
 *
 * @api
 */
interface ConnectionInterface
{
    /**
     * Metadata about this connection: local/peer addresses and TLS state.
     *
     * Provides diagnostics, logging, and security policy enforcement
     * (e.g., SSRF protection via peer address inspection).
     */
    public ConnectionMetadata $metadata { get; }

    /**
     * Execute one HTTP request/response exchange on this connection.
     *
     * Sends the request over the connection and returns a {@see Transaction}
     * containing the final response, any informational (1xx) responses
     * received before it, and any HTTP/2 server-pushed exchanges.
     *
     * The request body, if present, is streamed from the {@see Request::$body}
     * read handle during the exchange. The response body in the returned
     * transaction may also be streamed, callers should consume the
     * {@see Response::$body} read handle before the connection is reused or released.
     *
     * The exchange respects the provided cancellation token. If the token is
     * cancelled during the exchange, the connection may be left in an
     * indeterminate state and should not be reused.
     *
     * @param Request $request  The HTTP request to send. The request URL determines the Host header and request target.
     * @param ClientConfiguration $configuration    Client configuration governing transport behavior such as maximum response header size and response body size limits.
     * @param CancellationTokenInterface $cancellation  Token to cancel the exchange. Cancellation may occur at any point during sending or receiving.
     *
     * @throws RuntimeException If a transport-level error occurs, such as a connection reset, or unexpected disconnection during the exchange.
     * @throws ProtocolException If the server sends a malformed or unparseable response that violates the HTTP protocol.
     * @throws CancelledException If the token cancelled during the exchange.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.4 Message Exchanging
     */
    public function exchange(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): Transaction;

    /**
     * Prepare a completed transaction for return to the caller.
     *
     * For HTTP/1.x connections with pool release callbacks, this wraps the
     * response body so the connection is returned to the pool when the body
     * is consumed. For other connection types (HTTP/2), this returns the
     * transaction as-is since stream lifecycle is managed independently.
     *
     * Callers MUST return the result of this method, not the original
     * transaction. The finalized transaction may have a different response
     * body handle that manages connection lifecycle:
     *
     * ```php
     * return $connection->finalize($transaction);
     * ```
     *
     * {@see HandlerInterface} implementations MUST call this on the transaction
     * returned by {@see exchange()} before returning it. {@see MiddlewareInterface}
     * implementations that short-circuit the handler chain MUST also call this
     * on any transaction they construct directly.
     *
     * Middleware that calls {@see exchange()} multiple times for multi-step
     * protocols (e.g., NTLM authentication) MUST NOT call this on intermediate
     * responses, only on the final transaction being returned to the caller.
     */
    public function finalize(Transaction $transaction): Transaction;
}
