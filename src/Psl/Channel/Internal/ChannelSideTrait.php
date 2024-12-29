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
     * Returns the channel capacity if it’s bounded.
     *
     * @return null|positive-int
     *
     * @psalm-mutation-free
     */
    public function getCapacity(): null|int
    {
        /** @var null|int<1, max> */
        return $this->state->getCapacity();
    }

    public function close(): void
    {
        $this->state->close();
    }

    /**
     * @psalm-mutation-free
     */
    public function isClosed(): bool
    {
        return $this->state->isClosed();
    }

    /**
     * @return int<0, max>
     *
     * @psalm-mutation-free
     */
    public function count(): int
    {
        /** @var int<0, max> */
        return $this->state->count();
    }

    /**
     * @psalm-mutation-free
     */
    public function isFull(): bool
    {
        return $this->state->isFull();
    }

    /**
     * @psalm-mutation-free
     */
    public function isEmpty(): bool
    {
        return $this->state->isEmpty();
    }
}
