<?php

declare(strict_types=1);

namespace Psl\TCP;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Configuration for TCP connect operations.
 */
final readonly class ConnectConfiguration implements DefaultInterface
{
    /**
     * @param bool $noDelay Disable Nagle's algorithm for lower latency.
     * @param null|non-empty-string $bindTo Local address to bind to (e.g., "0.0.0.0:0", "192.168.1.1:0").
     */
    public function __construct(
        public bool $noDelay = false,
        public null|string $bindTo = null,
    ) {}

    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @psalm-mutation-free
     */
    public function withNoDelay(bool $noDelay): self
    {
        return new self($noDelay, $this->bindTo);
    }

    /**
     * @param non-empty-string|null $bindTo
     *
     * @return self
     */
    public function withBindTo(null|string $bindTo): self
    {
        return new self($this->noDelay, $bindTo);
    }
}
