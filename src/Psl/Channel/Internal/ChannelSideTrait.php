<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

/**
 * @template T of UnboundedChannelState|BoundedChannelState
 */
trait ChannelSideTrait
{
    /**
     * @var T
     */
    protected UnboundedChannelState|BoundedChannelState $state;

    /**
     * @return int<1, max>|null
     */
    public function getCapacity(): ?int
    {
        /** @var null|int<1, max> */
        return $this->state->getCapacity();
    }

    public function close(): void
    {
        $this->state->close();
    }

    public function isClosed(): bool
    {
        return $this->state->isClosed();
    }

    /**
     * @return int<0, max>
     */
    public function count(): int
    {
        /** @var int<0, max> */
        return $this->state->count();
    }

    public function isFull(): bool
    {
        return $this->state->isFull();
    }

    public function isEmpty(): bool
    {
        return $this->state->isEmpty();
    }
}
