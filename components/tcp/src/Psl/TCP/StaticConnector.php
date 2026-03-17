<?php

declare(strict_types=1);

namespace Psl\TCP;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;

/**
 * A connector that redirects all connections to a fixed host and port.
 *
 * Useful for testing or forwarding connections to a specific endpoint
 * regardless of the requested target.
 */
final readonly class StaticConnector implements ConnectorInterface
{
    /**
     * @param non-empty-string $host The fixed host to connect to.
     * @param int<0, 65535> $port The fixed port to connect to.
     * @param ConnectorInterface $connector The underlying connector to use.
     */
    public function __construct(
        private string $host,
        private int $port,
        private ConnectorInterface $connector = new Connector(),
    ) {}

    #[Override]
    public function connect(
        string $host,
        int $port,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface {
        return $this->connector->connect($this->host, $this->port, $cancellation);
    }
}
