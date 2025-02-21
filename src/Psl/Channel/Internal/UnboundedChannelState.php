<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Channel\ChannelInterface;
use Psl\Channel\Exception;
use Revolt\EventLoop\Suspension;

use function array_shift;
use function count;

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
final class UnboundedChannelState implements ChannelInterface
{
    /**
     * @var list<Suspension<mixed>>
     */
    private array $waitingForMessage = [];

    /**
     * @var array<array-key, T>
     */
    private array $messages = [];

    private bool $closed = false;

    /**
     * @param Suspension<mixed> $suspension
     */
    public function waitForMessage(Suspension $suspension): void
    {
        $this->waitingForMessage[] = $suspension;
    }

    /**
     * @return null
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function getCapacity(): null
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function close(): void
    {
        $this->closed = true;

        $suspensions = $this->waitingForMessage;
        $this->waitingForMessage = [];
        foreach ($suspensions as $suspension) {
            $suspension->throw(Exception\ClosedChannelException::forReceiving());
        }
    }

    /**
     * @psalm-mutation-free
     */
    #[\Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * @return int<0, max>
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function count(): int
    {
        return count($this->messages);
    }

    /**
     * @psalm-mutation-free
     */
    #[\Override]
    public function isFull(): bool
    {
        return false;
    }

    /**
     * @psalm-mutation-free
     */
    #[\Override]
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

        $this->messages[] = $message;
        $suspension = array_shift($this->waitingForMessage);
        $suspension?->resume(null);
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

        return array_shift($this->messages);
    }
}
