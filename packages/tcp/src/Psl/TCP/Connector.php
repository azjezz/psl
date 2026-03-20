<?php

declare(strict_types=1);

namespace Psl\TCP;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Psl\Default\DefaultInterface;

/**
 * Default TCP connector that establishes direct connections.
 */
final readonly class Connector implements ConnectorInterface, DefaultInterface
{
    public function __construct(
        private ConnectConfiguration $configuration = new ConnectConfiguration(),
    ) {}

    /**
     * @pure
     */
    #[Override]
    public static function default(): static
    {
        return new self();
    }

    #[Override]
    public function connect(
        string $host,
        int $port,
        CancellationTokenInterface $cancellation = new NullCancellationToken(),
    ): StreamInterface {
        return namespace\connect($host, $port, $this->configuration, $cancellation);
    }
}
