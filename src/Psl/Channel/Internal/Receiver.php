<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Async;
use Psl\Channel\Exception;
use Psl\Channel\ReceiverInterface;
use Revolt\EventLoop\Suspension;

/**
 * @template T
 *
 * @implements ReceiverInterface<T>
 */
final class Receiver implements ReceiverInterface
{
    /**
     * @var Async\Sequence<mixed, T>
     */
    private Async\Sequence $sequence;

    private null|Suspension $suspension = null;

    /**
     * @param ChannelState<T> $state
     */
    public function __construct(
        private ChannelState $state
    ) {
        $this->sequence = new Async\Sequence(
            /**
             * @return T
             */
            function () {
                // check empty before closed as a non-empty closed channel could still be used
                // for receiving.
                if ($this->state->isEmpty()) {
                    // the channel could have already been closed
                    // so check first, otherwise the event loop will hang, and exit unexpectedly
                    if ($this->state->isClosed()) {
                        throw Exception\ClosedChannelException::forReceiving();
                    }

                    /** @var Suspension<null> */
                    $this->suspension = Async\Scheduler::getSuspension();
                    $this->suspension->suspend();
                }

                /**
                 * @psalm-suppress MissingThrowsDocblock
                 *
                 * @var T
                 */
                return $this->state->receive();
            },
        );

        $this->state->addCloseListener(function () {
            if ($this->state->isEmpty()) {
                $this->sequence->cancel(Exception\ClosedChannelException::forReceiving());

                $this->suspension?->throw(Exception\ClosedChannelException::forReceiving());
                $this->suspension = null;
            }
        });

        $this->state->addSendListener(function () {
            $this->suspension?->resume();
            $this->suspension = null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function receive(): mixed
    {
        return $this->sequence->waitFor(null);
    }

    /**
     * {@inheritDoc}
     */
    public function tryReceive(): mixed
    {
        return $this->state->receive();
    }

    /**
     * {@inheritDoc}
     */
    public function getCapacity(): ?int
    {
        return $this->state->getCapacity();
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->state->close();
    }

    /**
     * {@inheritDoc}
     */
    public function isClosed(): bool
    {
        return $this->state->isClosed();
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->state->count();
    }

    /**
     * {@inheritDoc}
     */
    public function isFull(): bool
    {
        return $this->state->isFull();
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return $this->state->isEmpty();
    }
}
