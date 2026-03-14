<?php

declare(strict_types=1);

namespace Psl\Socks;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\TCP;
use SensitiveParameter;

/**
 * A TCP connector that tunnels connections through a SOCKS5 proxy.
 *
 * Implements {@see TCP\ConnectorInterface} so it can be used anywhere a TCP connector is expected.
 * This enables transparent proxy tunneling for any code that accepts a connector.
 *
 * Usage:
 *   $connector = new Socks\Connector('proxy.example.com', 1080, 'user', 'pass');
 *   $stream = $connector->connect('target.example.com', 443);
 */
final readonly class Connector implements TCP\ConnectorInterface
{
    /**
     * @param non-empty-string $proxyHost SOCKS5 proxy server hostname or IP.
     * @param int<0, 65535> $proxyPort SOCKS5 proxy server port.
     * @param non-empty-string|null $username Optional authentication username.
     * @param non-empty-string|null $password Optional authentication password.
     * @param TCP\ConnectorInterface $connector Connector used to reach the proxy server itself.
     */
    public function __construct(
        private string $proxyHost,
        private int $proxyPort,
        private null|string $username = null,
        #[SensitiveParameter]
        private null|string $password = null,
        private TCP\ConnectorInterface $connector = new TCP\Connector(),
    ) {}

    #[Override]
    public function connect(
        string $host,
        int $port,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): TCP\StreamInterface {
        // Connect to the SOCKS5 proxy
        $stream = $this->connector->connect($this->proxyHost, $this->proxyPort, $cancellation);

        // Perform the SOCKS5 handshake to tunnel to the target
        Internal\socks5_handshake($stream, $host, $port, $this->username, $this->password);

        // The stream is now tunneled to the target through the proxy
        return $stream;
    }
}
