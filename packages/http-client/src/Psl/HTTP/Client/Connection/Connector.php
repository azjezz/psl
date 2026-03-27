<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Connection;

use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H2\H2Connection;
use Psl\HTTP\Client\Internal\H2\H2Session;
use Psl\HTTP\Client\Internal\HttpTunnel;
use Psl\HTTP\Client\ProxyConfiguration;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\Network;
use Psl\Socks;
use Psl\TCP;
use Psl\TLS;
use Psl\Unix;

use function in_array;
use function Psl\HTTP\Client\Internal\resolve_protocol_versions;
use function Psl\HTTP\Client\Internal\should_tunnel;

/**
 * Direct connector that creates a fresh connection for each request without pooling or reuse.
 *
 * This is a stateless {@see ConnectorInterface} implementation that establishes a new
 * TCP (and optionally TLS) connection for every call to {@see connect()}. No idle pool
 * is maintained; each connection is used for exactly one exchange and then discarded.
 *
 * ## Connection strategies
 *
 * The connector selects one of three connection strategies based on the request URL
 * scheme and the client configuration:
 *
 * - **Unix socket**: When {@see ClientConfiguration::$unixSocket} is set, the connector
 *   bypasses DNS and TCP entirely, connecting to the specified Unix domain socket. HTTP/2
 *   prior-knowledge mode is used when the resolved versions contain only
 *   {@see ProtocolVersion::V20}; otherwise, HTTP/1.x is assumed.
 *
 * - **HTTPS (TLS)**: Establishes a TCP connection, performs a TLS handshake with ALPN
 *   negotiation, and creates either an HTTP/2 or HTTP/1.x connection based on the
 *   negotiated protocol. ALPN tokens ("h2", "http/1.1") are derived from the resolved
 *   protocol versions.
 *
 * - **Plaintext TCP**: Establishes a raw TCP connection. HTTP/2 prior-knowledge mode
 *   (h2c) is used when the resolved versions contain only {@see ProtocolVersion::V20};
 *   otherwise, an HTTP/1.x connection is created.
 *
 * ## Proxy support
 *
 * When {@see ClientConfiguration::$socksConfiguration} is configured, all TCP connections are routed
 * through a SOCKS5 proxy. When {@see ClientConfiguration::$proxy} is configured, HTTPS
 * connections use HTTP CONNECT tunneling while plain HTTP connections use forward proxying
 * with absolute-form request targets (RFC 7230 Section 5.3.2), unless the target host matches
 * one of the bypass rules in {@see ProxyConfiguration::$skipProxyFor}.
 *
 * ## When to use this connector
 *
 * Use this connector when connection reuse is not desired (e.g., single-request scripts,
 * testing, or scenarios where connections must not be shared). For production use with
 * multiple requests, prefer {@see PooledConnector} which provides idle connection reuse
 * and HTTP/2 session sharing.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9110#section-3.3 Connections and Transport
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4 HTTP/2 Prior Knowledge
 * @link https://datatracker.ietf.org/doc/html/rfc7301 TLS ALPN Extension
 *
 * @see PooledConnector For connection reuse and pooling.
 */
final readonly class Connector implements ConnectorInterface
{
    /**
     * The underlying TCP connector used to establish raw TCP connections.
     *
     * When no custom connector is provided, a default {@see TCP\Connector} is
     * created with TCP_NODELAY enabled to reduce latency for HTTP exchanges.
     */
    private TCP\ConnectorInterface $tcpConnector;

    /**
     * Create a new direct connector.
     *
     * @param null|TCP\ConnectorInterface $tcpConnector Custom TCP connector for establishing raw TCP connections.
     *        When {@see null}, a default connector with TCP_NODELAY enabled is created.
     */
    public function __construct(null|TCP\ConnectorInterface $tcpConnector = null)
    {
        $this->tcpConnector = $tcpConnector ?? new TCP\Connector(new TCP\ConnectConfiguration(noDelay: true));
    }

    /**
     * Establish a fresh connection to the server identified by the request URL.
     *
     * Creates a new connection on every call without checking any pool. The
     * connection strategy is selected based on the configuration and request URL:
     *
     * 1. If {@see ClientConfiguration::$unixSocket} is set, connects to the Unix socket.
     * 2. If the URL scheme is "https", performs TCP + TLS with ALPN negotiation.
     * 3. Otherwise, performs a plaintext TCP connection.
     *
     * @param Origin $origin The target origin (scheme, host, port) to connect to.
     * @param Request $request The HTTP request for protocol version resolution.
     * @param ClientConfiguration $configuration Client configuration for protocol preferences, TLS, and proxy settings.
     * @param CancellationTokenInterface $cancellation Token to cancel the connection at any stage.
     *
     * @return ConnectionInterface A fresh, exchange-ready connection.
     *
     * @throws Exception\ProtocolException If the requested protocol version is not supported or not permitted by the configuration.
     * @throws Network\Exception\RuntimeException If the TCP connection fails.
     * @throws TLS\Exception\HandshakeFailedException If the TLS handshake fails.
     * @throws Async\Exception\CancelledException If the cancellation token fires during any stage.
     */
    #[Override]
    public function connect(
        Origin $origin,
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): ConnectionInterface {
        $cancellation->throwIfCancelled();

        $versions = resolve_protocol_versions($request, $configuration);

        if ($configuration->unixSocket !== null) {
            return $this->connectUnix($configuration->unixSocket, $versions, $cancellation, $configuration);
        }

        if ($origin->scheme === 'https') {
            return $this->connectTLS($origin, $versions, $cancellation, $configuration);
        }

        return $this->connectTCP($origin, $versions, $cancellation, $configuration);
    }

    /**
     * Resolve the effective TCP connector, wrapping it in a SOCKS5 proxy connector if configured.
     *
     * @param ClientConfiguration $configuration The client configuration that may specify a SOCKS5 proxy.
     *
     * @return TCP\ConnectorInterface The connector to use for TCP connections.
     */
    private function resolveTcpConnector(ClientConfiguration $configuration): TCP\ConnectorInterface
    {
        if ($configuration->socksConfiguration !== null) {
            return new Socks\Connector($this->tcpConnector, $configuration->socksConfiguration);
        }

        return $this->tcpConnector;
    }

    /**
     * Establish a plaintext TCP connection and create the appropriate connection type.
     *
     * Applies HTTP forward proxying when configured and the target host is not
     * in the bypass list. For HTTP/2 prior-knowledge mode (only
     * {@see ProtocolVersion::V20} in the version list), an H2 session is
     * initialized directly on the TCP stream. Otherwise, an HTTP/1.x connection
     * is created.
     *
     * @param Origin $origin The target origin (scheme, host, port) derived from the request URL.
     * @param list<ProtocolVersion> $versions Resolved protocol versions determining the connection type.
     * @param CancellationTokenInterface $cancellation Token to cancel the connection.
     * @param ClientConfiguration $configuration Client configuration for proxy settings.
     *
     * @return ConnectionInterface An HTTP/1.x or HTTP/2 (h2c) connection.
     */
    private function connectTCP(
        Origin $origin,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        $connector = $this->resolveTcpConnector($configuration);
        $proxyConfiguration = $configuration->proxyConfiguration;
        if ($proxyConfiguration !== null && should_tunnel($origin->host, $proxyConfiguration->skipProxyFor)) {
            return $this->connectViaForwardProxy($connector, $proxyConfiguration, $configuration, $cancellation);
        }

        $stream = $connector->connect($origin->host, $origin->port, $cancellation);
        $metadata = new ConnectionMetadata($stream->getLocalAddress(), $stream->getPeerAddress(), null);

        if ($versions === [ProtocolVersion::V20]) {
            return $this->createH2Connection($stream, $metadata, $configuration);
        }

        return new H1Connection($stream, $metadata);
    }

    /**
     * Connect to an HTTP forward proxy for plaintext HTTP requests.
     *
     * The connection targets the proxy host:port directly. The H1 transport
     * will use absolute-form request-targets per RFC 7230 Section 5.3.2 and
     * include the Proxy-Authorization header if the proxy URL contains
     * credentials.
     *
     * @link https://datatracker.ietf.org/doc/html/rfc7230#section-5.3.2 absolute-form
     */
    private function connectViaForwardProxy(
        TCP\ConnectorInterface $connector,
        ProxyConfiguration $proxyConfiguration,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation,
    ): H1Connection {
        $proxyHost = $proxyConfiguration->url->authority->host->toString();
        $proxyPort =
            $proxyConfiguration->url->authority->port ?? ($proxyConfiguration->url->scheme === 'https' ? 443 : 80);
        $proxyTls = $proxyConfiguration->url->scheme === 'https';

        $stream = $connector->connect($proxyHost, $proxyPort, $cancellation);

        if ($proxyTls) {
            $tlsConnector = new TLS\Connector($configuration->tlsConfiguration);
            $stream = $tlsConnector->connect($stream, $proxyConfiguration->sni ?? $proxyHost, $cancellation);
        }

        $metadata = new ConnectionMetadata(
            $stream->getLocalAddress(),
            $stream->getPeerAddress(),
            $proxyTls && $stream instanceof TLS\StreamInterface ? $stream->getState() : null,
        );

        return new H1Connection(
            $stream,
            $metadata,
            onRelease: null,
            isForwardProxy: true,
            proxyAuthorization: $proxyConfiguration->authorization,
        );
    }

    /**
     * Establish a TLS connection with ALPN negotiation and create the appropriate connection type.
     *
     * Configures ALPN protocol tokens based on the resolved versions ("h2" for HTTP/2,
     * "http/1.1" for HTTP/1.x), performs a TCP connection (with optional tunneling),
     * then upgrades to TLS. The server's ALPN selection determines whether an HTTP/2
     * session or HTTP/1.x connection is created.
     *
     * @param Origin $origin The target origin (scheme, host, port) derived from the request URL.
     * @param list<ProtocolVersion> $versions Resolved protocol versions used to derive ALPN tokens.
     * @param CancellationTokenInterface $cancellation Token to cancel the connection or handshake.
     * @param ClientConfiguration $configuration Client configuration for TLS and proxy settings.
     *
     * @return ConnectionInterface An HTTP/1.x or HTTP/2 connection, depending on ALPN negotiation.
     */
    private function connectTLS(
        Origin $origin,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        $tlsConfig = $configuration->tlsConfiguration;

        $alpnProtocols = [];
        if (in_array(ProtocolVersion::V20, $versions, strict: true)) {
            $alpnProtocols[] = 'h2';
        }

        if (
            in_array(ProtocolVersion::V11, $versions, strict: true)
            || in_array(ProtocolVersion::V10, $versions, strict: true)
        ) {
            $alpnProtocols[] = 'http/1.1';
        }

        if ($alpnProtocols !== []) {
            $tlsConfig = $tlsConfig->withAlpnProtocols($alpnProtocols);
        }

        $connector = $this->resolveTcpConnector($configuration);

        if (
            $configuration->proxyConfiguration !== null
            && should_tunnel($origin->host, $configuration->proxyConfiguration->skipProxyFor)
        ) {
            $tcpStream = HttpTunnel::connect(
                $connector,
                $configuration->proxyConfiguration,
                $origin->host,
                $origin->port,
                $cancellation,
                $configuration->tlsConfiguration,
            );
        } else {
            $tcpStream = $connector->connect($origin->host, $origin->port, $cancellation);
        }

        $tlsConnector = new TLS\Connector($tlsConfig);
        $stream = $tlsConnector->connect($tcpStream, $origin->sniHost ?? $origin->host, $cancellation);
        $metadata = new ConnectionMetadata($stream->getLocalAddress(), $stream->getPeerAddress(), $stream->getState());

        if ($stream->getState()->alpnProtocol === 'h2' && in_array(ProtocolVersion::V20, $versions, strict: true)) {
            return $this->createH2Connection($stream, $metadata, $configuration);
        }

        return new H1Connection($stream, $metadata);
    }

    /**
     * Connect to a Unix domain socket and create the appropriate connection type.
     *
     * Bypasses DNS resolution and TCP connection entirely. For HTTP/2
     * prior-knowledge mode (only {@see ProtocolVersion::V20} in the version
     * list), an H2 session is initialized on the Unix stream. Otherwise, an
     * HTTP/1.x connection is created.
     *
     * @param non-empty-string $path Path to the Unix domain socket.
     * @param list<ProtocolVersion> $versions Resolved protocol versions determining the connection type.
     * @param CancellationTokenInterface $cancellation Token to cancel the connection.
     * @param ClientConfiguration $configuration Client configuration for H2 session settings.
     *
     * @return ConnectionInterface An HTTP/1.x or HTTP/2 connection over the Unix socket.
     */
    private function connectUnix(
        string $path,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        $stream = Unix\connect($path, $cancellation);
        $metadata = new ConnectionMetadata($stream->getLocalAddress(), $stream->getPeerAddress(), null);

        if ($versions === [ProtocolVersion::V20]) {
            return $this->createH2Connection($stream, $metadata, $configuration);
        }

        return new H1Connection($stream, $metadata);
    }

    /**
     * Initialize an HTTP/2 session on a network stream and return a connection for one stream.
     *
     * Creates an {@see H2Session} using the provided stream and H2 configuration,
     * then returns a new {@see H2Connection} representing one HTTP/2 stream on that
     * session. Since this connector does not pool, the session is not stored for reuse.
     *
     * @param Network\StreamInterface $stream The underlying TCP, TLS, or Unix stream.
     * @param ConnectionMetadata $metadata Transport-level metadata for the connection.
     * @param ClientConfiguration $configuration Client configuration providing H2 session settings.
     *
     * @return H2Connection A connection representing one HTTP/2 stream.
     */
    private function createH2Connection(
        Network\StreamInterface $stream,
        ConnectionMetadata $metadata,
        ClientConfiguration $configuration,
    ): H2Connection {
        $session = new H2Session($stream, $configuration->h2ClientConfiguration);

        return new H2Connection($session, $metadata);
    }
}
