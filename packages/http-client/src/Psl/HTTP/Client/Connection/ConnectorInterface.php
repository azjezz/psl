<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Connection;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception\ProtocolException;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\Network\Exception\RuntimeException;
use Psl\TLS\Exception\HandshakeFailedException;

/**
 * Creates protocol-aware connections for HTTP exchanges.
 *
 * The connector is responsible for the full connection establishment pipeline:
 * DNS resolution, TCP socket creation, TLS handshake (when the request URL
 * uses HTTPS), and application protocol negotiation via ALPN. The resulting
 * {@see ConnectionInterface} is ready for an HTTP exchange regardless of
 * the underlying protocol version.
 *
 * ## Protocol version resolution
 *
 * Two inputs determine which protocol versions are acceptable for the connection:
 *
 * 1. **Request version** ({@see Request::$protocolVersion}): acts as an explicit
 *    constraint when set to a non-default value. For example, a request with
 *    {@see ProtocolVersion::V20} demands an HTTP/2 connection.
 *
 * 2. **Configuration preferences** ({@see ClientConfiguration::$protocolVersions}):
 *    an ordered list of protocol versions the client is willing to use, from
 *    most preferred to least preferred. This drives ALPN negotiation during
 *    the TLS handshake.
 *
 * Implementations must resolve these two inputs as follows:
 *
 * - If the request version is the default ({@see ProtocolVersion::V11}), use
 *   the configuration preferences as-is.
 * - If the request version is non-default, verify it appears in the configuration
 *   preferences. If it does, use only that version. If it does not, throw
 *   {@see ProtocolException::forUnsupportedProtocol()}.
 * - If the resolved version is not supported by the implementation, throw
 *   {@see ProtocolException::forUnsupportedProtocol()}.
 * - If the resolved version list is empty, throw.
 *
 * ## Connection type mapping
 *
 * The resolved protocol versions determine the connection type:
 *
 * - **HTTP/1.0 or HTTP/1.1**: Create a TCP (or TLS) connection and return an
 *   {@see ConnectionInterface} that performs a single HTTP/1.x exchange per call.
 * - **HTTP/2 over TLS**: Advertise "h2" via ALPN during the TLS handshake. If
 *   the server negotiates "h2", initialize an HTTP/2 session (connection preface
 *   + SETTINGS exchange) and return an {@see ConnectionInterface} that performs
 *   one exchange on a multiplexed stream. Multiple connections may share the
 *   same underlying TCP/TLS socket.
 * - **HTTP/2 over plaintext** (h2c): Only when the resolved versions contain
 *   exclusively {@see ProtocolVersion::V20}. Initialize an HTTP/2 session
 *   directly on the TCP connection (prior-knowledge mode per RFC 9113 Section 3.4).
 * - **HTTP/3**: Establish a QUIC connection and advertise "h3" via ALPN.
 *   Return a {@see ConnectionInterface} that performs one exchange on a
 *   QUIC stream. Like HTTP/2, multiple connections may share the same
 *   underlying QUIC session.
 * - **Mixed preferences** (e.g. [V20, V11] over TLS): Advertise all applicable
 *   protocols via ALPN. The server chooses; the connector creates the
 *   appropriate connection type based on the negotiated protocol.
 *
 * ## Extension points
 *
 * Implementations may customize any stage of the pipeline:
 *
 * - Custom DNS resolution (e.g., DNS-over-HTTPS, async resolvers, caching).
 * - Connection pooling and reuse (see {@see PooledConnector}).
 * - Pre-connect security checks such as SSRF protection against resolved IPs.
 * - Proxy tunneling via CONNECT or SOCKS.
 * - QUIC/UDP transport for future HTTP/3 support.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.3 Connections and Transport
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4 HTTP/2 Prior Knowledge
 * @link https://datatracker.ietf.org/doc/html/rfc7301 TLS ALPN Extension
 *
 * @api
 */
interface ConnectorInterface
{
    /**
     * Establish a connection to the server identified by the given origin.
     *
     * The origin specifies where to connect (scheme, host, port). The host
     * may be a hostname or an already-resolved IP address (e.g., when a
     * DNS-resolving connector decorator has pre-resolved the hostname).
     *
     * The connection process involves multiple stages, each of which may fail
     * independently:
     *
     * 1. **Protocol resolution**: Determine the effective protocol version(s) from
     *    the request and configuration (see class-level documentation).
     * 2. **TCP connection**: Connect to the origin host and port.
     * 3. **TLS handshake** (HTTPS only): Perform the TLS handshake using
     *    {@see ClientConfiguration::$tlsConfiguration}. ALPN negotiation
     *    during this stage selects between HTTP/1.1 and HTTP/2.
     * 4. **Protocol initialization**: For HTTP/2, send the connection preface
     *    and exchange SETTINGS frames.
     *
     * The cancellation token is respected throughout all stages.
     *
     * @param Origin $origin The target origin (scheme, host, port) to connect to.
     * @param Request $request The HTTP request for protocol version resolution.
     * @param ClientConfiguration $configuration Client configuration governing protocol preferences, TLS settings, proxy configuration, and Unix socket path.
     * @param CancellationTokenInterface $cancellation Token to cancel the connection attempt at any stage.
     *
     * @return ConnectionInterface A protocol-aware connection ready for an HTTP exchange.
     *
     * @throws RuntimeException If the TCP connection fails.
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws ProtocolException If the requested protocol version is not supported or not permitted by the configuration.
     * @throws CancelledException If the cancellation token fires during any stage.
     */
    public function connect(
        Origin $origin,
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): ConnectionInterface;
}
