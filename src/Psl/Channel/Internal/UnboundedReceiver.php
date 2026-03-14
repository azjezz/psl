<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
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
     * @inheritDoc
     */
    #[Override]
    public function receive(CancellationTokenInterface $cancellation = new NullCancellationToken()): mixed
    {
        if ($this->suspension) {
            $cancellation->throwIfCancelled();

            $suspension = EventLoop::getSuspension();
            $this->suspension = $suspension;
            $this->state->waitForMessage($suspension);

            $id = $cancellation->subscribe(function (CancelledException $e) use ($suspension): void {
                $this->state->removeFromWaitingForMessage($suspension);
                $suspension->throw($e);
            });

            try {
                $suspension->suspend();
            } finally {
                $cancellation->unsubscribe($id);
            }
        }

        try {
            return $this->state->receive();
        } catch (Exception\EmptyChannelException) {
            $cancellation->throwIfCancelled();

            $suspension = EventLoop::getSuspension();
            $this->suspension = $suspension;
            $this->state->waitForMessage($suspension);

            $id = $cancellation->subscribe(function (CancelledException $e) use ($suspension): void {
                $this->state->removeFromWaitingForMessage($suspension);
                $suspension->throw($e);
            });

            try {
                $suspension->suspend();
            } finally {
                $cancellation->unsubscribe($id);
            }

            return $this->state->receive();
        } finally {
            $this->suspension = null;
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function tryReceive(): mixed
    {
        return $this->state->receive();
    }
}
