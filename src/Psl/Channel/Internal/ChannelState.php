<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\ChannelInterface;
use Psl\Channel\Exception;

use function array_shift;
use function count;

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
     * @var array<array-key, T>
     */
    private array $messages = [];

    private bool $closed = false;

    public function __construct(
        private ?int $capacity = null,
    ) {
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
        return count($this->messages);
    }

    /**
     * {@inheritDoc}
     */
    public function isFull(): bool
    {
        return $this->capacity && $this->capacity === $this->count();
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return 0 === count($this->messages);
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

        if (null === $this->capacity || $this->capacity > count($this->messages)) {
            $this->messages[] = $message;

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
        if ([] === $this->messages) {
            if ($this->closed) {
                throw Exception\ClosedChannelException::forReceiving();
            }

            throw Exception\EmptyChannelException::create();
        }

        return array_shift($this->messages);
    }
}
