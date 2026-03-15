<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception as RootException;
use Revolt\EventLoop;

use function array_search;
use function array_splice;
use function count;

/**
 * A group of concurrent tasks that can be awaited together.
 *
 * Deferred closures run concurrently. {@see awaitAll()} blocks until every
 * task has completed. If one or more tasks throw, all exceptions are collected
 * and thrown as a {@see Exception\CompositeException} after every task finishes.
 */
final class TaskGroup
{
    /**
     * @var int<0, max>
     */
    private int $pending = 0;

    /**
     * @var list<RootException>
     */
    private array $exceptions = [];

    /**
     * @var list<EventLoop\Suspension>
     */
    private array $waiters = [];

    /**
     * Defer a closure for concurrent execution.
     *
     * The closure will start running on the next tick of the event loop.
     *
     * @param (Closure(): void) $closure
     */
    public function defer(Closure $closure): void
    {
        $this->pending++;

        EventLoop::defer(function () use ($closure): void {
            try {
                $closure();
            } catch (RootException $e) {
                $this->exceptions[] = $e;
            } finally {
                // @mago-expect analysis:invalid-property-assignment-value
                $this->pending--;

                if (0 === $this->pending) {
                    $waiters = $this->waiters;
                    $this->waiters = [];

                    foreach ($waiters as $waiter) {
                        $waiter->resume(null);
                    }
                }
            }
        });
    }

    /**
     * Wait for all deferred tasks to complete.
     *
     * If any tasks threw, a {@see Exception\CompositeException} is thrown
     * containing all the exceptions, after every task has finished.
     *
     * @throws Exception\CompositeException If multiple tasks failed.
     * @throws Exception\CancelledException If the cancellation token is cancelled.
     */
    public function awaitAll(CancellationTokenInterface $cancellation = new NullCancellationToken()): void
    {
        if (0 !== $this->pending) {
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

        $exceptions = $this->exceptions;
        $this->exceptions = [];

        if ([] !== $exceptions) {
            if (1 === count($exceptions)) {
                throw $exceptions[0];
            }

            throw new Exception\CompositeException($exceptions);
        }
    }
}
