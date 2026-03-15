<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Exception\InvariantViolationException;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

use function array_search;
use function array_splice;
use function Psl\invariant_violation;

/**
 * A counter-based synchronization primitive.
 *
 * Use {@see add()} to increment the counter before starting work,
 * {@see done()} to decrement it when work completes, and
 * {@see wait()} to block until the counter reaches zero.
 */
final class WaitGroup
{
    /**
     * @var int<0, max>
     */
    private int $count = 0;

    /**
     * @var list<Suspension>
     */
    private array $waiters = [];

    /**
     * Increment the counter.
     *
     * Call this before starting each unit of work.
     */
    public function add(): void
    {
        $this->count++;
    }

    /**
     * Decrement the counter.
     *
     * Call this when a unit of work completes. When the counter reaches zero,
     * all fibers blocked in {@see wait()} are resumed.
     *
     * @throws InvariantViolationException If the counter is already zero.
     */
    public function done(): void
    {
        if (0 === $this->count) {
            invariant_violation('WaitGroup counter is already zero.');
        }

        $this->count--;

        if (0 === $this->count) {
            $waiters = $this->waiters;
            $this->waiters = [];

            foreach ($waiters as $waiter) {
                $waiter->resume(null);
            }
        }
    }

    /**
     * Block until the counter reaches zero.
     *
     * If the counter is already zero, this method returns immediately.
     *
     * @throws Exception\CancelledException If the cancellation token is cancelled while waiting.
     */
    public function wait(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if (0 === $this->count) {
            return;
        }

        if ($cancellation->cancellable) {
            $cancellation->throwIfCancelled();
        }

        $suspension = EventLoop::getSuspension();
        $this->waiters[] = $suspension;

        $id = null;
        if ($cancellation->cancellable) {
            $id = $cancellation->subscribe(function (Exception\CancelledException $e) use ($suspension): void {
                $index = array_search($suspension, $this->waiters, true);
                if (false !== $index) {
                    array_splice($this->waiters, $index, 1);
                    $suspension->throw($e);
                }
            });
        }

        try {
            $suspension->suspend();
        } finally {
            if (null !== $id) {
                $cancellation->unsubscribe($id);
            }
        }
    }

    /**
     * Get the current counter value.
     *
     * @return int<0, max>
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
