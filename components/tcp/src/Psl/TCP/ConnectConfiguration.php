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
     */
    public function __construct(
        public bool $noDelay = false,
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
        return new self($noDelay);
    }
}
