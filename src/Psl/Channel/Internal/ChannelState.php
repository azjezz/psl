<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\ChannelInterface;
use Psl\Channel\Exception;
use Psl\DataStructure\Queue;
use Psl\DataStructure\QueueInterface;

/**
 * @template T
 *
 * @implements ChannelInterface<T>
 *
 * @internal
 */
final class ChannelState implements ChannelInterface
{
    /**
     * @var QueueInterface<T>
     */
    private QueueInterface $messages;

    private bool $closed = false;

    public function __construct(
        private ?int $capacity = null,
    ) {
        $this->messages = new Queue();
    }

    /**
     * {@inheritDoc}
     */
    public function getCapacity(): ?int
    {
        return $this->capacity;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->closed = true;
    }

    /**
     * {@inheritDoc}
     */
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * {@inheritDoc}
     */
    public function count(): int
    {
        return $this->messages->count();
    }

    /**
     * {@inheritDoc}
     */
    public function isFull(): bool
    {
        if (null === $this->capacity) {
            return false;
        }

        return $this->capacity === $this->count();
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return 0 === $this->messages->count();
    }

    /**
     * @param T $message
     *
     * @throws Exception\ClosedChannelException If the channel is closed.
     * @throws Exception\FullChannelException If the channel is full.
     */
    public function send(mixed $message): void
    {
        if ($this->closed) {
            throw Exception\ClosedChannelException::forSending();
        }

        if (null === $this->capacity || $this->capacity > $this->count()) {
            $this->messages->enqueue($message);

            return;
        }

        throw Exception\FullChannelException::ofCapacity($this->capacity);
    }

    /**
     * @throws Exception\ClosedChannelException If the channel is closed, and there's no more messages to receive.
     * @throws Exception\EmptyChannelException If the channel is empty.
     *
     * @return T
     */
    public function receive(): mixed
    {
        $empty = 0 === $this->count();
        $closed = $this->closed;
        if ($closed && $empty) {
            throw Exception\ClosedChannelException::forReceiving();
        }

        if ($empty) {
            throw Exception\EmptyChannelException::create();
        }

        /** @psalm-suppress MissingThrowsDocblock */
        return $this->messages->dequeue();
    }
}
