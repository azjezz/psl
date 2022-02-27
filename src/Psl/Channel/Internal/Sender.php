<?php

declare(strict_types=1);

namespace Psl\Channel\Internal;

use Psl\Async;
use Psl\Channel\Exception;
use Psl\Channel\SenderInterface;
use Revolt\EventLoop\Suspension;

/**
 * @template T
 *
 * @implements SenderInterface<T>
 */
final class Sender implements SenderInterface
{
    /**
     * @var Async\Sequence<T, void>
     */
    private Async\Sequence $sequence;

    private null|Suspension $suspension = null;

    /**
     * @param ChannelState<T> $state
     */
    public function __construct(
        private ChannelState $state
    ) {
        $this->sequence = new Async\Sequence(
            /**
             * @param T $message
             */
            function (mixed $message): void {
                if ($this->state->isFull() && !$this->state->isClosed()) {
                    /** @var Suspension<null> */
                    $this->suspension = Async\Scheduler::getSuspension();
                    $this->suspension->suspend();
                }

                /** @psalm-suppress MissingThrowsDocblock */
                $this->state->send($message);
            },
        );

        $this->state->addCloseListener(function () {
            $this->suspension?->throw(Exception\ClosedChannelException::forSending());
            $this->suspension = null;
        });

        $this->state->addReceiveListener(function () {
            $this->suspension?->resume(null);
            $this->suspension = null;
        });
    }

    /**
     * {@inheritDoc}
     */
    public function send(mixed $message): void
    {
        $this->sequence->waitFor($message);
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
