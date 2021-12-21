<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Closure;
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
     * @var list<(Closure(): void)>
     */
    private array $closeListeners = [];

    /**
     * @var list<(Closure(): void)>
     */
    private array $receiveListeners = [];

    /**
     * @var list<(Closure(): void)>
     */
    private array $sendListeners = [];

    /**
     * @var array<array-key, T>
     */
    private array $messages = [];

    private bool $closed = false;

    /**
     * @param null|positive-int $capacity
     */
    public function __construct(
        private ?int $capacity = null,
    ) {
    }

    /**
     * @param (Closure(): void) $listener
     */
    public function addCloseListener(Closure $listener): void
    {
        $this->closeListeners[] = $listener;
    }

    /**
     * @param (Closure(): void) $listener
     */
    public function addSendListener(Closure $listener): void
    {
        $this->sendListeners[] = $listener;
    }

    /**
     * @param (Closure(): void) $listener
     */
    public function addReceiveListener(Closure $listener): void
    {
        $this->receiveListeners[] = $listener;
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

        foreach ($this->closeListeners as $listener) {
            $listener();
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
            foreach ($this->sendListeners as $listener) {
                $listener();
            }

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

        $item = array_shift($this->messages);
        foreach ($this->receiveListeners as $listener) {
            $listener();
        }

        return $item;
    }
}
