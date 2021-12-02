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
            $this->deferred?->error(Exception\ClosedChannelException::forSending());
            $this->deferred = null;
        });

        $this->state->addReceiveListener(function () {
            $this->deferred?->complete(null);
            $this->deferred = null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function send(mixed $message): void
    {
        // there's a pending operation? wait for it.
        $this->deferred?->getAwaitable()->await();

        // the channel could have already been closed
        // so check first, otherwise the event loop will hang, and exit unexpectedly
        if ($this->state->isClosed()) {
            throw Exception\ClosedChannelException::forSending();
        }

        if ($this->state->isFull()) {
            /** @var Async\Deferred<null> */
            $this->deferred = new Async\Deferred();

            $this->deferred->getAwaitable()->await();
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
