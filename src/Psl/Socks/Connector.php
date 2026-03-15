<?php

declare(strict_types=1);

namespace Psl\Socks;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\TCP;

/**
 * A TCP connector that tunnels connections through a SOCKS5 proxy.
 *
 * Implements {@see TCP\ConnectorInterface} so it can be used anywhere a TCP connector is expected.
 * This enables transparent proxy tunneling for any code that accepts a connector.
 *
 * Usage:
 *   $connector = new Socks\Connector(
 *       new TCP\Connector(),
 *       new Socks\Configuration('proxy.example.com', 1080, 'user', 'pass'),
 *   );
 *
 *   $stream = $connector->connect('target.example.com', 443);
 */
final readonly class Connector implements TCP\ConnectorInterface
{
    public function __construct(
        private TCP\ConnectorInterface $connector,
        private Configuration $configuration,
    ) {}

    /**
     * @inheritDoc
     */
    #[Override]
    public function connect(
        string $host,
        int $port,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): TCP\StreamInterface {
        $stream = $this->connector->connect(
            $this->configuration->proxyHost,
            $this->configuration->proxyPort,
            $cancellation,
        );

        Internal\socks5_handshake($stream, $host, $port, $this->configuration, $cancellation);

        return $stream;
    }
}
