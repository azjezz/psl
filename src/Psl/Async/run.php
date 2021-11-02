<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Async\Exception\TimeoutException;
use Throwable;

/**
 * Create a new fiber asynchronously using the given callable.
 *
 * @template T
 *
 * @param (callable(): T) $callable
 *
 * @return Awaitable<T>
 */
function run(callable $callable, ?int $timeout_ms = null): Awaitable
{
    $state = new Internal\State();

    Scheduler::defer(static function () use ($callable, $state): void {
        try {
            $state->complete($callable());
        } catch (Throwable $throwable) {
            $state->error($throwable);
        }
    });

    if (null !== $timeout_ms) {
        $id = Scheduler::delay($timeout_ms, static function () use ($state): void {
            $state->error(new TimeoutException());
        });

        $state->subscribe(static function () use ($id): void {
            Scheduler::cancel($id);
        });
    }

    return new Awaitable($state);
}
