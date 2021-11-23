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
        $this->deferred?->getAwaitable()->then(static fn() => null, static fn() => null)->ignore()->await();

        if ($this->state->isEmpty()) {
            $this->deferred = new Async\Deferred();

            Async\Scheduler::repeat(0.0001, function (string $identifier): void {
                if (null === $this->deferred) {
                    Async\Scheduler::cancel($identifier);
                }

                if ($this->state->isClosed()) {
                    /**
                     * Channel has been closed from the receiving side.
                     *
                     * @psalm-suppress PossiblyNullReference
                     */
                    if (!$this->deferred->isComplete()) {
                        /** @psalm-suppress PossiblyNullReference */
                        $this->deferred->error(Exception\ClosedChannelException::forSending());
                    }

                    return;
                }

                if (!$this->state->isEmpty()) {
                    /** @psalm-suppress PossiblyNullReference */
                    $this->deferred->complete(null);
                }
            });

            /** @psalm-suppress PossiblyNullReference */
            $this->deferred->getAwaitable()->await();
            $this->deferred = null;
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
        $this->deferred?->error(Exception\ClosedChannelException::forSending());

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
