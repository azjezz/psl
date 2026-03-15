<?php

declare(strict_types=1);

namespace Psl\TCP;

use Override;
use Psl\Default\DefaultInterface;

/**
 * Configuration for TCP listen operations.
 */
final readonly class ListenConfiguration implements DefaultInterface
{
    /**
     * @param bool $noDelay Disable Nagle's algorithm for lower latency.
     * @param bool $reuseAddress Allow reuse of local addresses.
     * @param bool $reusePort Allow multiple sockets to bind to the same port.
     * @param int<1, max> $backlog Maximum length of the queue of pending connections.
     * @param int<1, max> $idleConnections Maximum number of idle connections to buffer.
     */
    public function __construct(
        public bool $noDelay = false,
        public bool $reuseAddress = false,
        public bool $reusePort = false,
        public int $backlog = 512,
        public int $idleConnections = 256,
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
        return new self($noDelay, $this->reuseAddress, $this->reusePort, $this->backlog, $this->idleConnections);
    }

    /**
     * @psalm-mutation-free
     */
    public function withReuseAddress(bool $reuseAddress): self
    {
        return new self($this->noDelay, $reuseAddress, $this->reusePort, $this->backlog, $this->idleConnections);
    }

    /**
     * @psalm-mutation-free
     */
    public function withReusePort(bool $reusePort): self
    {
        return new self($this->noDelay, $this->reuseAddress, $reusePort, $this->backlog, $this->idleConnections);
    }

    /**
     * @param int<1, max> $backlog
     *
     * @psalm-mutation-free
     */
    public function withBacklog(int $backlog): self
    {
        return new self($this->noDelay, $this->reuseAddress, $this->reusePort, $backlog, $this->idleConnections);
    }

    /**
     * @param int<1, max> $idleConnections
     *
     * @psalm-mutation-free
     */
    public function withIdleConnections(int $idleConnections): self
    {
        return new self($this->noDelay, $this->reuseAddress, $this->reusePort, $this->backlog, $idleConnections);
    }
}
