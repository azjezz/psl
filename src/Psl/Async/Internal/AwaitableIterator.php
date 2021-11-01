<?php

declare(strict_types=1);

namespace Psl\Async\Internal;

use Psl;
use Psl\Async\Awaitable;
use Psl\Iter;
use Throwable;

/**
 * @template Tk
 * @template Tv
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class AwaitableIterator
{
    /**
     * @var AwaitableIteratorQueue<Tk, Tv>
     */
    private AwaitableIteratorQueue $queue;

    /**
     * @var null|Awaitable<void>|Awaitable<null>|Awaitable<array{0: Tk, 1: Awaitable<Tv>}>
     */
    private ?Awaitable $complete = null;

    public function __construct()
    {
        $this->queue = new AwaitableIteratorQueue();
    }

    /**
     * @param State<Tv> $state
     * @param Tk $key
     * @param Awaitable<Tv> $awaitable
     *
     * @throws Psl\Exception\InvariantViolationException If the iterator has already been marked as complete.
     */
    public function enqueue(State $state, mixed $key, Awaitable $awaitable): void
    {
        Psl\invariant(null === $this->complete, 'Iterator has already been marked as complete');

        $queue = $this->queue; // Using separate object to avoid a circular reference.
        $id = $state->subscribe(
            /**
             * @param Tv|null $_result
             */
            static function (
                ?Throwable $_error,
                mixed $_result,
                string $id
            ) use (
                $key,
                $awaitable,
                $queue
            ): void {
                unset($queue->pending[$id]);

                if ($queue->suspension) {
                    $queue->suspension->resume([$key, $awaitable]);
                    $queue->suspension = null;
                    return;
                }

                $queue->items[] = [$key, $awaitable];
            }
        );

        $queue->pending[$id] = $state;
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If the iterator has already been marked as complete.
     */
    public function complete(): void
    {
        Psl\invariant(null === $this->complete, 'Iterator has already been marked as complete');

        $this->complete = Awaitable::complete(null);

        if (!$this->queue->pending && $this->queue->suspension) {
            $this->queue->suspension->resume(null);
            $this->queue->suspension = null;
        }
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If the iterator has already been marked as complete.
     */
    public function error(Throwable $exception): void
    {
        Psl\invariant(null === $this->complete, 'Iterator has already been marked as complete');

        $this->complete = Awaitable::error($exception);

        if (!$this->queue->pending && $this->queue->suspension) {
            $this->queue->suspension->throw($exception);
            $this->queue->suspension = null;
        }
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If {@see consume()} is called concurrently.
     *
     * @return null|array{0: Tk, 1: Awaitable<Tv>}
     */
    public function consume(): ?array
    {
        Psl\invariant(null === $this->queue->suspension, 'Concurrent consume() operations are not supported');

        if (Iter\is_empty($this->queue->items)) {
            if (Iter\is_empty($this->queue->pending) && $this->complete !== null) {
                return $this->complete->await();
            }

            $this->queue->suspension = Psl\Async\Scheduler::createSuspension();

            /** @var null|array{0: Tk, 1: Awaitable<Tv>} */
            return $this->queue->suspension->suspend();
        }

        $key = (int) Iter\first_key($this->queue->items);
        $item = $this->queue->items[$key];

        unset($this->queue->items[$key]);

        /** @var null|array{0: Tk, 1: Awaitable<Tv>} */
        return $item;
    }

    public function __destruct()
    {
        foreach ($this->queue->pending as $id => $state) {
            $state->unsubscribe($id);
        }
    }
}
