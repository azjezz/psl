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

    $timeout_watcher = null;
    if (null !== $timeout_ms) {
        $timeout_watcher = Scheduler::delay($timeout_ms, static function () use ($state): void {
            $state->error(new TimeoutException());
        });

        Scheduler::unreference($timeout_watcher);
    }

    Scheduler::defer(static function () use ($callable, $state, $timeout_watcher): void {
        try {
            $result = $callable();
            if (null !== $timeout_watcher) {
                Scheduler::cancel($timeout_watcher);
            }

            if ($state->isComplete()) {
                // timed-out.
                return;
            }

            $state->complete($result);
        } catch (Throwable $throwable) {
            $state->error($throwable);
        }
    });


    return new Awaitable($state);
}
