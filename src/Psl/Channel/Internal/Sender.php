<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Async;
use Psl\Channel\Exception;
use Psl\Channel\SenderInterface;

/**
 * @template T
 *
 * @implements SenderInterface<T>
 */
final class Sender implements SenderInterface
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
    public function send(mixed $message): void
    {
        // there's a pending operation? wait for it.
        $this->deferred?->getAwaitable()->then(static fn() => null, static fn() => null)->await();

        if ($this->state->isFull()) {
            $this->deferred = new Async\Deferred();

            $identifier = Async\Scheduler::repeat(0.000000001, function (): void {
                if ($this->state->isClosed()) {
                    /**
                     * Channel has been closed from the receiver side.
                     *
                     * @psalm-suppress PossiblyNullReference
                     */
                    if (!$this->deferred->isComplete()) {
                        /** @psalm-suppress PossiblyNullReference */
                        $this->deferred->error(Exception\ClosedChannelException::forSending());
                    }

                    return;
                }

                if (!$this->state->isFull()) {
                    /** @psalm-suppress PossiblyNullReference */
                    $this->deferred->complete(null);
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
        $this->state->send($message);
    }

    /**
     * {@inheritDoc}
     */
    public function trySend(mixed $message): void
    {
        $this->state->send($message);
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
