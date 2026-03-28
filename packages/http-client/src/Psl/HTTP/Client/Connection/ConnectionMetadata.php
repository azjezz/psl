<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Connection;

use Psl\Network;
use Psl\TLS;

/**
 * Metadata about an acquired HTTP connection.
 *
 * Captures the transport-level details of a connection at the time it is
 * acquired from the connector. This includes the local and remote network
 * addresses and, for TLS connections, the negotiated session state.
 *
 * Connection metadata is made available via the {@see SendConfiguration::$onConnection}
 * callback, which fires immediately after a connection is acquired, whether
 * it is a freshly established connection or one reused from the pool.
 *
 * ## Local address
 *
 * The local address is the socket endpoint on this machine: the IP address
 * and ephemeral port assigned by the operating system. For TCP-based
 * connections (HTTP/1.x and HTTP/2), this is the local TCP socket address.
 * For QUIC-based connections (HTTP/3), this would be the local UDP address.
 *
 * ## Peer address
 *
 * The peer address is the resolved address of the remote server after DNS
 * resolution, the actual IP address and port the connection was established
 * to. This may differ from the hostname in the request URL when the server
 * is behind a load balancer, CDN, or when connecting through a proxy.
 *
 * Middleware can inspect the peer address to enforce security policies such
 * as SSRF protection by checking against denied IP ranges before the
 * exchange is performed.
 *
 * ## TLS state
 *
 * For HTTPS connections, the TLS state provides details about the negotiated
 * session: protocol version, cipher suite, peer certificates, and the
 * ALPN-negotiated application protocol. For plaintext HTTP connections,
 * the TLS state is {@see null}.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.3 Connections and Transport
 *
 * @api
 */
final readonly class ConnectionMetadata
{
    /**
     * The local network address of this connection.
     *
     * This is the address of the local socket endpoint, the IP address and
     * ephemeral port assigned by the operating system for this side of the
     * connection.
     *
     * For TCP-based connections (HTTP/1.1 and HTTP/2), this is the local
     * TCP socket address. For QUIC-based connections (HTTP/3), this is the
     * local UDP socket address.
     */
    public Network\Address $localAddress;

    /**
     * The remote network address of the connected peer.
     *
     * This is the resolved address of the server after DNS resolution, the
     * actual IP address and port the connection was established to. This may
     * differ from the hostname in the request URL when the server is behind
     * a load balancer, CDN, or when connecting through a proxy.
     *
     * For TCP-based connections (HTTP/1.1 and HTTP/2), this is the remote
     * TCP socket address. For QUIC-based connections (HTTP/3), this is the
     * remote UDP socket address.
     */
    public Network\Address $peerAddress;

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
    public null|TLS\ConnectionState $tlsState;

    /**
     * Create a new connection metadata instance.
     *
     * @param Network\Address $localAddress The local socket address (IP and ephemeral port) assigned by the operating system.
     * @param Network\Address $peerAddress The resolved remote address (IP and port) of the connected peer.
     * @param null|TLS\ConnectionState $tlsState The negotiated TLS session state, or {@see null} for plaintext connections.
     */
    public function __construct(
        Network\Address $localAddress,
        Network\Address $peerAddress,
        null|TLS\ConnectionState $tlsState = null,
    ) {
        $this->localAddress = $localAddress;
        $this->peerAddress = $peerAddress;
        $this->tlsState = $tlsState;
    }
}
