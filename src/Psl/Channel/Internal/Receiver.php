<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Async;
use Psl\Channel\Exception;
use Psl\Channel\ReceiverInterface;

/**
 * @template T
 *
 * @implements ReceiverInterface<T>
 */
final class Receiver implements ReceiverInterface
{
    /**
     * @var null|Async\Deferred<T>
     */
    private ?Async\Deferred $deferred = null;

    /**
     * @param ChannelState<T> $state
     */
    public function __construct(
        private ChannelState $state
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function receive(): mixed
    {
        // there's a pending operation? wait for it.
        $this->deferred?->getAwaitable()->then(static fn() => null, static fn() => null)->await();

        if ($this->state->isEmpty()) {
            $this->deferred = new Async\Deferred();

            $identifier = Async\Scheduler::repeat(0.000000001, function (): void {
                if (!$this->state->isEmpty()) {
                    /** @psalm-suppress PossiblyNullReference */
                    $this->deferred->complete(null);

                    return;
                }

                /**
                 * Channel has been closed from the sender side.
                 *
                 * @psalm-suppress PossiblyNullReference
                 */
                if ($this->state->isClosed() && !$this->deferred->isComplete()) {
                    /** @psalm-suppress PossiblyNullReference */
                    $this->deferred->error(Exception\ClosedChannelException::forReceiving());
                }
            });

            try {
                /** @psalm-suppress PossiblyNullReference */
                $this->deferred->getAwaitable()->await();
            } finally {
                $this->deferred = null;
                Async\Scheduler::cancel($identifier);
            }
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return $this->state->receive();
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
        if ($this->state->isEmpty()) {
            $this->deferred?->error(Exception\ClosedChannelException::forReceiving());
        }

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
