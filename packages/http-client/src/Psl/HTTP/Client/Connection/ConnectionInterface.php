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
use Psl\Network;
use Psl\TLS;

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
     * The local network address of this connection.
     *
     * This is the address of the local socket endpoint, the IP address and
     * port assigned by the operating system for this side of the connection.
     * Useful for diagnostics, logging, and network-level observability.
     *
     * For TCP-based connections (HTTP/1.1 and HTTP/2), this is the local
     * TCP socket address. For QUIC-based connections (HTTP/3), this is the
     * local UDP socket address.
     */
    public Network\Address $localAddress { get; }

    /**
     * The remote network address of the connected peer.
     *
     * This is the resolved address of the server after DNS resolution, the
     * actual IP address and port the connection was established to. This may
     * differ from the hostname in the request URL when the server is behind
     * a load balancer, CDN, or when connecting through a proxy.
     *
     * Connection-level middleware can inspect this address to enforce security
     * policies such as SSRF protection by checking against denied IP ranges
     * before the exchange is performed.
     *
     * For TCP-based connections (HTTP/1.1 and HTTP/2), this is the remote
     * TCP socket address. For QUIC-based connections (HTTP/3), this is the
     * remote UDP socket address.
     */
    public Network\Address $peerAddress { get; }

    /**
     * The TLS connection state, or {@see null} for plaintext connections.
     *
     * When present, this provides details about the negotiated TLS session
     * including the protocol version, cipher suite, peer certificates, and
     * ALPN-negotiated application protocol.
     *
     * For HTTP/2 over TLS (h2), the ALPN protocol will be "h2". For HTTP/1.1
     * over TLS, it will typically be "http/1.1". For HTTP/3, which uses
     * QUIC with TLS 1.3 integrated into the transport handshake, this will
     * reflect the QUIC-TLS session state.
     *
     * This is {@see null} for plaintext HTTP connections (h2c, HTTP/1.1
     * without TLS).
     *
     * @link https://datatracker.ietf.org/doc/html/rfc8446 TLS 1.3
     * @link https://datatracker.ietf.org/doc/html/rfc7301 TLS ALPN Extension
     */
    public null|TLS\ConnectionState $tlsState { get; }

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
}
