<?php

declare(strict_types=1);

namespace Psl\Async\Internal;

use Psl;
use Psl\Async\Awaitable;
use Revolt\EventLoop;
use Throwable;

use function array_shift;

/**
 * The following class was derived from code of Amphp.
 *
 * https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/lib/Internal/FutureIteratorQueue.php
 *
 * Code subject to the MIT license (https://github.com/amphp/amp/blob/ac89b9e2ee04228e064e424056a08590b0cdc7b3/LICENSE).
 *
 * Copyright (c) 2015-2021 Amphp ( https://amphp.org )
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
final class AwaitableIterator<Tk, Tv>
{
    private readonly AwaitableIteratorQueue<Tk, Tv> $queue;

    /**
     * @var null|Awaitable<void>|Awaitable<null>|Awaitable<array{Tk, Awaitable<Tv>}>
     */
    private null|Awaitable<void>|Awaitable<null>|Awaitable<array> $complete = null;

    public function __construct()
    {
        $this->queue = new AwaitableIteratorQueue::<Tk, Tv>();
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If the iterator has already been marked as complete.
     */
    public function enqueue(State<Tv> $state, Tk $key, Awaitable<Tv> $awaitable): void
    {
        if (null !== $this->complete) {
            Psl\invariant_violation('Iterator has already been marked as complete');
        }

        $queue = $this->queue; // Using separate object to avoid a circular reference.
        $id = $state->subscribe(
            static function (null|Throwable $_error, Tv|null $_result, string $id) use ($key, $awaitable, $queue): void {
                unset($queue->pending[$id]);

                if ($queue->suspension) {
                    $queue->suspension->resume([$key, $awaitable]);
                    $queue->suspension = null;
                    return;
                }

                $queue->items[] = [$key, $awaitable];
            },
        );

        $queue->pending[$id] = $state;
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If the iterator has already been marked as complete.
     */
    public function complete(): void
    {
        if (null !== $this->complete) {
            Psl\invariant_violation('Iterator has already been marked as complete');
        }

        $this->complete = Awaitable::<null>::complete(null);

        if (!$this->queue->pending && $this->queue->suspension) {
            $this->queue->suspension->resume();
            $this->queue->suspension = null;
        }
    }

    /**
     * @throws Psl\Exception\InvariantViolationException If the iterator has already been marked as complete.
     */
    public function error(Throwable $exception): void
    {
        if (null !== $this->complete) {
            Psl\invariant_violation('Iterator has already been marked as complete');
        }

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
    public function consume(): null|array
    {
        if (null !== $this->queue->suspension) {
            Psl\invariant_violation('Concurrent consume() operations are not supported');
        }

        if ([] === $this->queue->items) {
            if (null !== $this->complete && [] === $this->queue->pending) {
                return $this->complete->await();
            }

            $this->queue->suspension = EventLoop::getSuspension();

            /** @var null|array{0: Tk, 1: Awaitable<Tv>} */
            return $this->queue->suspension->suspend();
        }

        /** @var null|array{0: Tk, 1: Awaitable<Tv>} */
        return array_shift($this->queue->items);
    }

    public function __destruct()
    {
        foreach ($this->queue->pending as $id => $state) {
            $state->unsubscribe($id);
        }
    }
}
