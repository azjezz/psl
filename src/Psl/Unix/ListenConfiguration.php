<?php

declare(strict_types=1);

namespace Psl\Unix;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Configuration for Unix domain socket listen operations.
 */
final readonly class ListenConfiguration implements DefaultInterface
{
    /**
     * @param int<1, max> $backlog Maximum length of the queue of pending connections.
     * @param int<1, max> $idleConnections Maximum number of idle connections to buffer.
     */
    public function __construct(
        public int $backlog = 512,
        public int $idleConnections = 256,
    ) {}

    #[Override]
    public static function default(): static
    {
        return new self();
    }

    /**
     * @param int<1, max> $backlog
     *
     * @psalm-mutation-free
     */
    public function withBacklog(int $backlog): self
    {
        return new self($backlog, $this->idleConnections);
    }

    /**
     * @param int<1, max> $idleConnections
     *
     * @psalm-mutation-free
     */
    public function withIdleConnections(int $idleConnections): self
    {
        return new self($this->backlog, $idleConnections);
    }
}
