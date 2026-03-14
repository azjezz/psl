<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Override;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Channel\Exception;
use Psl\Channel\SenderInterface;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

/**
 * @template T
 *
 * @implements SenderInterface<T>
 */
final class BoundedSender implements SenderInterface
{
    /**
     * @use ChannelSideTrait<BoundedChannelState<T>>
     */
    use ChannelSideTrait;

    private null|Suspension $suspension = null;

    /**
     * @param BoundedChannelState<T> $state
     */
    public function __construct(BoundedChannelState $state)
    {
        $this->state = $state;
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function send(mixed $message, CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if ($this->suspension) {
            $cancellation->throwIfCancelled();

            $suspension = EventLoop::getSuspension();
            $this->suspension = $suspension;
            $this->state->waitForSpace($suspension);

            $id = $cancellation->subscribe(function (CancelledException $e) use ($suspension): void {
                $this->state->removeFromWaitingForSpace($suspension);
                $suspension->throw($e);
            });

            try {
                $suspension->suspend();
            } finally {
                $cancellation->unsubscribe($id);
            }
        }

        try {
            $this->state->send($message);
        } catch (Exception\FullChannelException) {
            $cancellation->throwIfCancelled();

            $suspension = EventLoop::getSuspension();
            $this->suspension = $suspension;
            $this->state->waitForSpace($suspension);

            $id = $cancellation->subscribe(function (CancelledException $e) use ($suspension): void {
                $this->state->removeFromWaitingForSpace($suspension);
                $suspension->throw($e);
            });

            try {
                $suspension->suspend();
            } finally {
                $cancellation->unsubscribe($id);
            }

            $this->state->send($message);
        } finally {
            $this->suspension = null;
        }
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function trySend(mixed $message): void
    {
        $this->state->send($message);
    }
}
