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
     * @var null|Async\Deferred<null>
     */
    private ?Async\Deferred $deferred = null;

    /**
     * @param ChannelState<T> $state
     */
    public function __construct(
        private ChannelState $state
    ) {
        $this->state->addCloseListener(function () {
            if ($this->state->isEmpty()) {
                $this->deferred?->error(Exception\ClosedChannelException::forReceiving());
                $this->deferred = null;
            }
        });

        $this->state->addSendListener(function () {
            $this->deferred?->complete(null);
            $this->deferred = null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function receive(): mixed
    {
        // there's a pending operation? wait for it.
        $this->deferred?->getAwaitable()->await();

        // check empty before closed as a non-empty closed channel could still be used
        // for receiving.
        if ($this->state->isEmpty()) {
            // the channel could have already been closed
            // so check first, otherwise the event loop will hang, and exit unexpectedly
            if ($this->state->isClosed()) {
                throw Exception\ClosedChannelException::forReceiving();
            }

            /** @var Async\Deferred<null> */
            $this->deferred = new Async\Deferred();

            $this->deferred->getAwaitable()->await();
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
