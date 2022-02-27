<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\Exception;
use Psl\Channel\ReceiverInterface;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

/**
 * @template T
 *
 * @implements ReceiverInterface<T>
 */
final class UnboundedReceiver implements ReceiverInterface
{
    /**
     * @use ChannelSideTrait<UnboundedChannelState<T>>
     */
    use ChannelSideTrait;

    private null|Suspension $suspension = null;

    /**
     * @param UnboundedChannelState<T> $state
     */
    public function __construct(UnboundedChannelState $state)
    {
        $this->state = $state;
    }

    /**
     * {@inheritDoc}
     */
    public function receive(): mixed
    {
        if ($this->suspension) {
            $this->suspension = $suspension = EventLoop::getSuspension();
            $this->state->waitForMessage($suspension);
            $suspension->suspend();
        }

        try {
            return $this->state->receive();
        } catch (Exception\EmptyChannelException) {
            $this->suspension = $suspension = EventLoop::getSuspension();
            $this->state->waitForMessage($suspension);
            $suspension->suspend();

            /** @psalm-suppress MissingThrowsDocblock */
            return $this->state->receive();
        } finally {
            $this->suspension = null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function tryReceive(): mixed
    {
        return $this->state->receive();
    }
}
