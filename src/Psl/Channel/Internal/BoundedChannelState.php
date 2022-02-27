<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\ChannelInterface;
use Psl\Channel\Exception;
use Revolt\EventLoop\Suspension;

use function array_shift;

/**
 * @template T
 *
 * @implements ChannelInterface<T>
 *
 * @internal
 *
 * @psalm-suppress LessSpecificReturnStatement
 * @psalm-suppress MoreSpecificReturnType
 */
final class BoundedChannelState implements ChannelInterface
{
    /**
     * @var list<Suspension<mixed>>
     */
    private array $waitingForMessage = [];

    /**
     * @var list<Suspension<mixed>>
     */
    private array $waitingForSpace = [];

    /**
     * @var array<array-key, T>
     */
    private array $messages = [];

    private int $size = 0;

    public bool $closed = false;

    /**
     * @param positive-int $capacity
     */
    public function __construct(
        private int $capacity
    ) {
    }

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param Suspension<mixed> $suspension
     */
    public function waitForSpace(Suspension $suspension): void
    {
        $this->waitingForSpace[] = $suspension;
    }

    /**
     * @param Suspension<mixed> $suspension
     */
    public function waitForMessage(Suspension $suspension): void
    {
        $this->waitingForMessage[] = $suspension;
    }

    /**
     * {@inheritDoc}
     */
    public function getCapacity(): int
    {
        return $this->capacity;
    }

    /**
     * {@inheritDoc}
     */
    public function close(): void
    {
        $this->closed = true;

        $suspensions = $this->waitingForSpace;
        $this->waitingForSpace = [];
        foreach ($suspensions as $suspension) {
            $suspension->throw(Exception\ClosedChannelException::forSending());
        }

        $suspensions = $this->waitingForMessage;
        $this->waitingForMessage = [];
        foreach ($suspensions as $suspension) {
            $suspension->throw(Exception\ClosedChannelException::forReceiving());
        }
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
        return $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function isFull(): bool
    {
        return $this->capacity === $this->size;
    }

    /**
     * {@inheritDoc}
     */
    public function isEmpty(): bool
    {
        return !$this->messages;
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

        if ($this->capacity === $this->size) {
            throw Exception\FullChannelException::ofCapacity($this->capacity);
        }

        $this->messages[] = $message;
        $this->size++;

        if ($suspension = array_shift($this->waitingForMessage)) {
            $suspension->resume(null);
        }
    }

    /**
     * @throws Exception\ClosedChannelException If the channel is closed, and there's no more messages to receive.
     * @throws Exception\EmptyChannelException If the channel is empty.
     *
     * @return T
     */
    public function receive(): mixed
    {
        if (!$this->messages) {
            if ($this->closed) {
                throw Exception\ClosedChannelException::forReceiving();
            }

            throw Exception\EmptyChannelException::create();
        }

        $item = array_shift($this->messages);
        $this->size--;

        if ($suspension = array_shift($this->waitingForSpace)) {
            $suspension->resume(null);
        }

        return $item;
    }
}
