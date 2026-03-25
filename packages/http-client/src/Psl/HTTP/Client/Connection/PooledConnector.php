<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Connection;

use Closure;
use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\HTTP\Client\ClientConfiguration;
use Psl\HTTP\Client\Exception;
use Psl\HTTP\Client\Internal\H1\H1Connection;
use Psl\HTTP\Client\Internal\H2\H2Connection;
use Psl\HTTP\Client\Internal\H2\H2Session;
use Psl\HTTP\Client\Internal\HttpTunnel;
use Psl\HTTP\Client\Internal\Origin;
use Psl\HTTP\Message\ProtocolVersion;
use Psl\HTTP\Message\Request;
use Psl\Network;
use Psl\Network\Exception\RuntimeException;
use Psl\Socks;
use Psl\TCP;
use Psl\TLS;
use Psl\TLS\Exception\HandshakeFailedException;
use Psl\Unix;
use Throwable;

use function array_key_exists;
use function array_pop;
use function array_shift;
use function count;
use function in_array;
use function Psl\HTTP\Client\Internal\resolve_protocol_versions;
use function Psl\HTTP\Client\Internal\should_tunnel;

/**
 * Connection-pooling connector that reuses HTTP/1.x connections and shares HTTP/2 sessions.
 *
 * This is the default {@see ConnectorInterface} implementation used by {@see \Psl\HTTP\Client\Client}.
 * It maintains a pool of idle connections keyed by origin (scheme + host + port) and
 * automatically selects the appropriate protocol based on the resolved protocol versions
 * and ALPN negotiation results.
 *
 * ## HTTP/1.x connection pooling
 *
 * For HTTP/1.x connections, the connector maintains an idle pool of up to
 * {@see MAX_IDLE_CONNECTIONS} TCP streams per origin. When a request completes, the
 * underlying stream is returned to the idle pool (via a release callback) for reuse by
 * subsequent requests to the same origin. Idle connections that are closed by the server
 * or garbage-collected are automatically pruned on checkout. When the idle pool is full,
 * the oldest connections are evicted.
 *
 * Idle streams are held as strong references to keep them alive for reuse.
 * Streams that are closed by the server are pruned on checkout.
 *
 * ## HTTP/2 session sharing
 *
 * HTTP/2 multiplexes multiple requests over a single TCP connection. The connector
 * maintains one {@see H2Session} per origin and returns a new {@see H2Connection}
 * (representing one HTTP/2 stream) for each request. All concurrent requests to the
 * same origin share the same session and underlying TCP socket.
 *
 * If the session is closed (e.g., due to a GOAWAY frame or connection error), it is
 * removed from the pool and a new connection is established on the next request.
 *
 * ## Connection coalescing for HTTPS
 *
 * When multiple fibers concurrently request connections to the same HTTPS origin, the
 * first fiber performs the TLS handshake and ALPN negotiation. If HTTP/2 is negotiated,
 * waiting fibers reuse the established session instead of opening redundant connections.
 * If HTTP/1.1 is negotiated, waiting fibers are notified to establish their own
 * connections.
 *
 * This coalescing prevents a thundering-herd of TLS handshakes when many requests target
 * the same origin simultaneously.
 *
 * ## Unix socket connections
 *
 * When the configuration specifies a Unix domain socket, the connector bypasses DNS
 * resolution and TCP connection, connecting directly to the socket. HTTP/2
 * prior-knowledge mode is used when the resolved protocol versions contain only
 * {@see ProtocolVersion::V20}.
 *
 * ## Protocol version resolution
 *
 * Protocol version resolution follows the rules documented in {@see ConnectorInterface}.
 * For HTTPS connections, ALPN tokens ("h2", "http/1.1") are derived from the resolved
 * versions and advertised during the TLS handshake. The server's ALPN selection
 * determines which connection type is created.
 *
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.2 HTTP/2 Connection Preface
 * @link https://datatracker.ietf.org/doc/html/rfc9113#section-3.4 HTTP/2 Prior Knowledge
 * @link https://datatracker.ietf.org/doc/html/rfc7301 TLS ALPN Extension
 *
 * @api
 */
final class PooledConnector implements ConnectorInterface
{
    private TCP\ConnectorInterface $tcpConnector;

    /**
     * Maximum number of idle HTTP/1.x connections retained per origin.
     *
     * When the idle pool for an origin reaches this limit, the oldest idle
     * connections are evicted (closed) to make room for newly released ones.
     */
    private const int MAX_IDLE_CONNECTIONS = 32;

    /**
     * Idle HTTP/1.x streams available for reuse, keyed by origin string.
     *
     * Streams are held as strong references to keep them alive for reuse.
     * Closed streams are pruned during checkout.
     *
     * @var array<string, list<Network\StreamInterface>>
     */
    private array $h1Idle = [];

    /**
     * Active HTTP/2 sessions keyed by origin string.
     *
     * Each entry contains the session, local address, peer address, and TLS
     * state. Multiple {@see H2Connection} instances may share the same session.
     *
     * @var array<string, array{H2Session, Network\Address, Network\Address, null|TLS\ConnectionState}>
     */
    private array $h2Sessions = [];

    /**
     * Pending connection awaitables for HTTP/2 session coalescing.
     *
     * When one fiber is establishing an HTTPS connection to an origin, other fibers
     * targeting the same origin wait on this awaitable instead of opening redundant
     * connections. If HTTP/2 is negotiated, the waiting fibers reuse the session.
     *
     * @var array<string, Async\Awaitable<array{H2Session, Network\Address, Network\Address, null|TLS\ConnectionState}>>
     */
    private array $pendingConnections = [];

    public function __construct(null|TCP\ConnectorInterface $tcpConnector = null)
    {
        $this->tcpConnector = $tcpConnector ?? new TCP\Connector(new TCP\ConnectConfiguration(noDelay: true));
    }

    /**
     * Resolve the effective TCP connector for a request, applying SOCKS proxy if configured.
     */
    private function resolveTcpConnector(ClientConfiguration $configuration): TCP\ConnectorInterface
    {
        if ($configuration->proxy !== null) {
            return new Socks\Connector($this->tcpConnector, $configuration->proxy);
        }

        return $this->tcpConnector;
    }

    /**
     * Obtain a connection from the pool or establish a new one.
     *
     * The connection strategy depends on the origin and negotiated protocol:
     *
     * 1. **Unix socket**: Bypasses the pool entirely; connects directly to the socket.
     * 2. **Existing HTTP/2 session**: If an active session exists for the origin, a
     *    new stream is created on it without any network I/O.
     * 3. **Pending HTTPS connection**: If another fiber is already connecting to the
     *    same HTTPS origin, waits for it to complete. If HTTP/2 was negotiated, shares
     *    the session; otherwise, establishes a new connection.
     * 4. **Idle HTTP/1.x connection**: If an idle stream is available in the pool,
     *    checks it out for reuse.
     * 5. **New connection**: Establishes a new TCP (+TLS for HTTPS) connection and
     *    negotiates the protocol via ALPN.
     *
     * @param Request $request The HTTP request. The URL determines the target origin.
     * @param ClientConfiguration $configuration Client configuration for protocol preferences, TLS settings, and Unix socket path.
     * @param CancellationTokenInterface $cancellation Token to cancel the connection attempt.
     *
     * @throws Exception\RequestException If the request has no URL.
     * @throws Exception\ProtocolException If the requested protocol version is not supported or not permitted by the configuration.
     * @throws RuntimeException If the TCP connection fails.
     * @throws HandshakeFailedException If the TLS handshake fails.
     * @throws CancelledException If the cancellation token fires during any stage.
     */
    #[Override]
    public function connect(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): ConnectionInterface {
        $url = $request->url ?? throw Exception\RequestException::forMissingUrl();
        $versions = resolve_protocol_versions($request, $configuration);

        if ($configuration->unixSocket !== null) {
            return $this->connectUnix($configuration->unixSocket, $versions, $cancellation, $configuration);
        }

        $origin = Origin::fromUrl($url);
        $key = $origin->toString();

        if (array_key_exists($key, $this->h2Sessions)) {
            [$session, $local, $peer, $tls] = $this->h2Sessions[$key];
            if ($session->isClosed()) {
                unset($this->h2Sessions[$key]);
            } else {
                return new H2Connection($session, $local, $peer, $tls, $this->h2Reconnect(...));
            }
        }

        if (array_key_exists($key, $this->pendingConnections)) {
            [$session, $local, $peer, $tls] = $this->pendingConnections[$key]->await();
            return new H2Connection($session, $local, $peer, $tls, $this->h2Reconnect(...));
        }

        $supportsH1 =
            in_array(ProtocolVersion::V11, $versions, strict: true)
            || in_array(ProtocolVersion::V10, $versions, strict: true);

        if ($supportsH1) {
            $stream = $this->checkoutH1($key);
            if ($stream !== null) {
                return new H1Connection($stream, $this->h1ReleaseCallback($key));
            }
        }

        if ($origin->scheme === 'https') {
            return $this->connectCoalesced($origin, $key, $versions, $cancellation, $configuration);
        }

        // h2c (prior knowledge) - needs coalescing like HTTPS so concurrent
        // fibers share the multiplexed session instead of each opening a connection.
        if ($versions === [ProtocolVersion::V20]) {
            return $this->connectCoalesced($origin, $key, $versions, $cancellation, $configuration);
        }

        return $this->connectTCP($origin, $key, $versions, $cancellation, $configuration);
    }

    /**
     * Establish a coalesced connection with H2 session sharing.
     *
     * Used for both HTTPS (TLS + ALPN) and h2c (plaintext prior-knowledge).
     * Registers a pending connection awaitable so other fibers targeting the same
     * origin can wait for the result. If HTTP/2 is negotiated (or known via
     * prior-knowledge), the session is shared with waiting fibers. If HTTP/1.1
     * is negotiated, waiting fibers are notified to establish their own connections.
     *
     * @param list<ProtocolVersion> $versions Resolved protocol versions.
     */
    private function connectCoalesced(
        Origin $origin,
        string $key,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        /** @var Async\Deferred<array{H2Session, Network\Address, Network\Address, null|TLS\ConnectionState}> $deferred */
        $deferred = new Async\Deferred();
        $awaitable = $deferred->getAwaitable();
        $awaitable->ignore();
        $this->pendingConnections[$key] = $awaitable;

        try {
            $connection = $origin->scheme === 'https'
                ? $this->connectTLS($origin, $key, $versions, $cancellation, $configuration)
                : $this->connectTCP($origin, $key, $versions, $cancellation, $configuration);

            if ($connection instanceof H2Connection && array_key_exists($key, $this->h2Sessions)) {
                // H2 - share the session with waiting fibers.
                [$session, $local, $peer, $tlsState] = $this->h2Sessions[$key];
                $deferred->complete([$session, $local, $peer, $tlsState]);
            } else {
                // H1 over TLS - connections can't be shared. Error
                // waiting fibers so they establish their own.
                $deferred->error(new RuntimeException('ALPN negotiated HTTP/1.1; connection cannot be shared.'));
            }

            return $connection;
        } catch (Throwable $e) {
            $deferred->error($e);
            throw $e;
        } finally {
            unset($this->pendingConnections[$key]);
        }
    }

    /**
     * Establish a plaintext TCP connection and create the appropriate connection type.
     *
     * For HTTP/2 prior-knowledge mode (only {@see ProtocolVersion::V20} in the version
     * list), an H2 session is initialized directly on the TCP stream. Otherwise, an
     * HTTP/1.x connection is created.
     *
     * @param list<ProtocolVersion> $versions Resolved protocol versions.
     */
    private function connectTCP(
        Origin $origin,
        string $key,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        $connector = $this->resolveTcpConnector($configuration);

        if ($configuration->tunnel !== null && should_tunnel($origin->host, $configuration->noTunneling)) {
            $stream = HttpTunnel::connect(
                $connector,
                $configuration->tunnel,
                $origin->host,
                $origin->port,
                $cancellation,
                $configuration->tlsConfiguration,
            );
        } else {
            $stream = $connector->connect($origin->host, $origin->port, $cancellation);
        }

        if ($versions === [ProtocolVersion::V20]) {
            return $this->createH2Connection($stream, $key, $configuration);
        }

        return new H1Connection($stream, $this->h1ReleaseCallback($key));
    }

    /**
     * Establish a TLS connection with ALPN negotiation and create the appropriate connection type.
     *
     * Configures ALPN protocol tokens based on the resolved versions ("h2" for HTTP/2,
     * "http/1.1" for HTTP/1.x), performs the TLS handshake, and creates either an H2
     * or H1 connection based on the negotiated protocol.
     *
     * @param list<ProtocolVersion> $versions Resolved protocol versions for ALPN negotiation.
     */
    private function connectTLS(
        Origin $origin,
        string $key,
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

        if ($configuration->tunnel !== null && should_tunnel($origin->host, $configuration->noTunneling)) {
            $tcpStream = HttpTunnel::connect(
                $connector,
                $configuration->tunnel,
                $origin->host,
                $origin->port,
                $cancellation,
                $configuration->tlsConfiguration,
            );
        } else {
            $tcpStream = $connector->connect($origin->host, $origin->port, $cancellation);
        }

        $tlsConnector = new TLS\Connector($tlsConfig);
        $stream = $tlsConnector->connect($tcpStream, $origin->host, $cancellation);

        if ($stream->getState()->alpnProtocol === 'h2' && in_array(ProtocolVersion::V20, $versions, strict: true)) {
            return $this->createH2Connection($stream, $key, $configuration);
        }

        return new H1Connection($stream, $this->h1ReleaseCallback($key));
    }

    /**
     * Connect to a Unix domain socket and create the appropriate connection type.
     *
     * Bypasses DNS resolution and TCP connection. For HTTP/2 prior-knowledge mode
     * (only {@see ProtocolVersion::V20} in the version list), an H2 session is
     * initialized on the Unix stream. Otherwise, an HTTP/1.x connection is created.
     *
     * @param non-empty-string $path Path to the Unix domain socket.
     * @param list<ProtocolVersion> $versions Resolved protocol versions.
     */
    private function connectUnix(
        string $path,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        $stream = Unix\connect($path, $cancellation);

        if ($versions === [ProtocolVersion::V20]) {
            return $this->createH2Connection($stream, 'unix://' . $path, $configuration);
        }

        return new H1Connection($stream);
    }

    /**
     * Initialize an HTTP/2 session on a network stream and register it in the session pool.
     *
     * Creates an {@see H2Session}, stores it in the session pool keyed by origin, and
     * returns a new {@see H2Connection} representing one stream on the session.
     */
    private function createH2Connection(
        Network\StreamInterface $stream,
        string $key,
        ClientConfiguration $configuration,
    ): H2Connection {
        $session = new H2Session($stream, $configuration->h2);

        $local = $stream->getLocalAddress();
        $peer = $stream->getPeerAddress();
        $tls = $stream instanceof TLS\StreamInterface ? $stream->getState() : null;

        $this->h2Sessions[$key] = [$session, $local, $peer, $tls];

        return new H2Connection($session, $local, $peer, $tls, $this->h2Reconnect(...));
    }

    /**
     * Reconnect closure for H2 connections.
     *
     * When an H2 session dies (GOAWAY, connection close), this is called by
     * {@see H2Connection::exchange()} to transparently obtain a fresh connection
     * from the pool. The caller never sees the session lifecycle.
     */
    private function h2Reconnect(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation,
    ): ConnectionInterface {
        return $this->connect($request, $configuration, $cancellation);
    }

    /**
     * Create a release callback that returns an HTTP/1.x stream to the idle pool.
     *
     * When the H1 connection is done with a stream, it calls this closure to return
     * the stream to the idle pool for the given origin. If the stream is closed, it
     * is discarded. If the idle pool is full, the oldest idle connection is evicted.
     *
     * @return (Closure(Network\StreamInterface): void)
     */
    private function h1ReleaseCallback(string $key): Closure
    {
        return function (Network\StreamInterface $stream) use ($key): void {
            if ($stream->isClosed()) {
                return;
            }

            // Evict oldest idle connections when over the cap.
            $this->h1Idle[$key] ??= [];
            while (count($this->h1Idle[$key]) >= self::MAX_IDLE_CONNECTIONS) {
                $evicted = array_shift($this->h1Idle[$key]);
                $evicted?->close();
            }

            $this->h1Idle[$key][] = $stream;
        };
    }

    /**
     * Attempt to check out an idle HTTP/1.x stream from the pool for the given origin.
     *
     * Pops streams from the idle pool (most recently returned first) and skips any
     * that have been closed or garbage-collected. Returns the first usable stream,
     * or {@see null} if none is available.
     */
    private function checkoutH1(string $key): null|Network\StreamInterface
    {
        while (array_key_exists($key, $this->h1Idle) && $this->h1Idle[$key] !== []) {
            $stream = array_pop($this->h1Idle[$key]);

            if ($this->h1Idle[$key] === []) {
                unset($this->h1Idle[$key]);
            }

            if (!$stream->isClosed()) {
                return $stream;
            }
        }

        return null;
    }
}
