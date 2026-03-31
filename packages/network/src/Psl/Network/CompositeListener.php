<?php

declare(strict_types=1);

namespace Psl\Network;

use Override;
use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\NullCancellationToken;
use Revolt\EventLoop\Suspension;
use Throwable;
use WeakMap;

use function array_map;
use function array_shift;

/**
 * A listener that accepts connections from multiple inner listeners concurrently.
 *
 * When {@see accept()} is called and no backlog is available, each inner listener
 * that doesn't already have a pending accept gets one in-flight accept fiber.
 * The first to return is delivered; others fill the backlog (bounded by N where
 * N = number of listeners) for subsequent calls.
 *
 * Backpressure propagates naturally: if the composite consumer is slow, inner
 * listeners' accept() calls block, their own backlogs fill, and they stop
 * accepting from the OS.
 *
 * @api
 */
final class CompositeListener implements ListenerInterface
{
    /**
     * @var non-empty-list<ListenerInterface>
     */
    private readonly array $listeners;

    private bool $closed = false;

    /**
     * Accepted streams waiting to be consumed. Bounded by the number of listeners.
     *
     * @var list<StreamInterface>
     */
    private array $backlog = [];

    /**
     * Tracks which listeners have an in-flight accept fiber.
     *
     * @var array<int, true>
     */
    private array $pending = [];

    /**
     * @var WeakMap<Suspension<null>, true>
     */
    private WeakMap $signal;

    /**
     * @param non-empty-list<ListenerInterface> $listeners
     */
    public function __construct(array $listeners)
    {
        $this->listeners = $listeners;
        $this->signal = new WeakMap();
    }

    /**
     * Accept the next connection from any of the inner listeners.
     *
     * @throws Exception\AlreadyStoppedException If all listeners have been stopped.
     * @throws Async\Exception\CancelledException If the cancellation token is cancelled while waiting.
     */
    #[Override]
    public function accept(CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface
    {
        if ($this->closed) {
            throw new Exception\AlreadyStoppedException('All listeners have been stopped.');
        }

        if ($this->backlog !== []) {
            return array_shift($this->backlog);
        }

        $this->startPendingAccepts();

        $cancellation->throwIfCancelled();

        /** @var Suspension<null> $signal */
        $signal = Async\Scheduler::getSuspension();
        $this->signal[$signal] = true;
        $subscription = $cancellation->subscribe(function (Async\Exception\CancelledException $e) use ($signal): void {
            unset($this->signal[$signal]);
            $signal->throw($e);
        });

        try {
            $signal->suspend();
        } finally {
            $cancellation->unsubscribe($subscription);
        }

        if ($this->backlog !== []) {
            return array_shift($this->backlog);
        }

        throw new Exception\AlreadyStoppedException('All listeners have been stopped.');
    }

    /**
     * Returns the local address of the first inner listener.
     */
    #[Override]
    public function getLocalAddress(): Address
    {
        return $this->listeners[0]->getLocalAddress();
    }

    /**
     * Returns the local addresses of all inner listeners.
     *
     * @return non-empty-list<Address>
     */
    public function getLocalAddresses(): array
    {
        return array_map(
            static fn(ListenerInterface $listener): Address => $listener->getLocalAddress(),
            $this->listeners,
        );
    }

    #[Override]
    public function isClosed(): bool
    {
        return $this->closed;
    }

    /**
     * Close all inner listeners and stop accepting connections.
     */
    #[Override]
    public function close(): void
    {
        if ($this->closed) {
            return;
        }

        $this->closed = true;

        foreach ($this->backlog as $stream) {
            $stream->close();
        }

        $this->backlog = [];

        foreach ($this->listeners as $listener) {
            $listener->close();
        }

        $signals = $this->signal;
        $this->signal = new WeakMap();
        foreach ($signals as $signal => $_) {
            $signal->resume(null);
        }
    }

    /**
     * Start an accept fiber for each listener that doesn't already have one.
     */
    private function startPendingAccepts(): void
    {
        foreach ($this->listeners as $i => $listener) {
            if (isset($this->pending[$i])) {
                continue;
            }

            $this->pending[$i] = true;

            Async\Scheduler::defer(function () use ($i, $listener): void {
                try {
                    $stream = $listener->accept();
                } catch (Throwable) {
                    unset($this->pending[$i]);
                    $this->notifySignal();
                    return;
                }

                unset($this->pending[$i]);
                $this->backlog[] = $stream;
                $this->notifySignal();
            });
        }
    }

    /**
     * Notify the waiting accept() call that something happened.
     *
     * @mago-expect lint:loop-does-not-iterate
     */
    private function notifySignal(): void
    {
        foreach ($this->signal as $signal => $_) {
            unset($this->signal[$signal]);
            $signal?->resume(null);
            break;
        }
    }
}
