<?php

declare(strict_types=1);

namespace Psl\HTTP\Client\Connection;

use Override;
use Psl\Async\CancellationTokenInterface;
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
use Psl\Socks;
use Psl\TCP;
use Psl\TLS;
use Psl\Unix;

use function in_array;
use function Psl\HTTP\Client\Internal\resolve_protocol_versions;
use function Psl\HTTP\Client\Internal\should_tunnel;

/**
 * Creates a fresh connection for each request - no pooling or reuse.
 *
 * Supports SOCKS5 proxying via {@see ClientConfiguration::$proxy} and HTTP
 * CONNECT tunneling via {@see ClientConfiguration::$tunnel}, with bypass
 * rules in {@see ClientConfiguration::$noTunneling}.
 *
 * @see PooledConnector For connection reuse and pooling.
 */
final readonly class Connector implements ConnectorInterface
{
    private TCP\ConnectorInterface $tcpConnector;

    public function __construct(null|TCP\ConnectorInterface $tcpConnector = null)
    {
        $this->tcpConnector = $tcpConnector ?? new TCP\Connector(new TCP\ConnectConfiguration(noDelay: true));
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function connect(
        Request $request,
        ClientConfiguration $configuration,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): ConnectionInterface {
        $url = $request->url ?? throw Exception\RequestException::forMissingUrl();
        $origin = Origin::fromUrl($url);

        $versions = resolve_protocol_versions($request, $configuration);

        if ($configuration->unixSocket !== null) {
            return $this->connectUnix($configuration->unixSocket, $versions, $cancellation, $configuration);
        }

        if ($origin->scheme === 'https') {
            return $this->connectTLS($origin, $versions, $cancellation, $configuration);
        }

        return $this->connectTCP($origin, $versions, $cancellation, $configuration);
    }

    private function resolveTcpConnector(ClientConfiguration $configuration): TCP\ConnectorInterface
    {
        if ($configuration->proxy !== null) {
            return new Socks\Connector($this->tcpConnector, $configuration->proxy);
        }

        return $this->tcpConnector;
    }

    /**
     * @param list<ProtocolVersion> $versions
     */
    private function connectTCP(
        Origin $origin,
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
            return $this->createH2Connection($stream, $configuration);
        }

        return new H1Connection($stream);
    }

    /**
     * @param list<ProtocolVersion> $versions
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
            return $this->createH2Connection($stream, $configuration);
        }

        return new H1Connection($stream);
    }

    /**
     * @param non-empty-string $path
     * @param list<ProtocolVersion> $versions
     */
    private function connectUnix(
        string $path,
        array $versions,
        CancellationTokenInterface $cancellation,
        ClientConfiguration $configuration,
    ): ConnectionInterface {
        $stream = Unix\connect($path, $cancellation);

        if ($versions === [ProtocolVersion::V20]) {
            return $this->createH2Connection($stream, $configuration);
        }

        return new H1Connection($stream);
    }

    private function createH2Connection(
        Network\StreamInterface $stream,
        ClientConfiguration $configuration,
    ): H2Connection {
        $session = new H2Session($stream, $configuration->h2);

        return new H2Connection(
            $session,
            $stream->getLocalAddress(),
            $stream->getPeerAddress(),
            $stream instanceof TLS\StreamInterface ? $stream->getState() : null,
        );
    }
}
