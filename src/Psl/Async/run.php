<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl;
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
function run(callable $callable, ?float $timeout = null): Awaitable
{
    $state = new Internal\State();

    /** @var Psl\Ref<string|null> $timeout_watcher */
    $timeout_watcher = new Psl\Ref(null);
    /** @var Psl\Ref<string|null> $delay_watcher */
    $delay_watcher = new Psl\Ref(null);

    if (null !== $timeout) {
        $timeout_watcher->value = Scheduler::delay($timeout, static function () use ($state, $delay_watcher, $timeout_watcher): void {
            if (null !== $delay_watcher->value) {
                $delay_watcher_value = $delay_watcher->value;
                $delay_watcher->value = null;
                Scheduler::cancel($delay_watcher_value);
            }

            $timeout_watcher->value = null;
            $state->error(new TimeoutException());
        });

        Scheduler::unreference($timeout_watcher->value);
    }

    $delay_watcher->value = Scheduler::defer(static function () use ($callable, $state, $timeout_watcher): void {
        $exception = null;
        $result = null;
        try {
            $result = $callable();
        } catch (Throwable $throwable) {
            $exception = $throwable;
        }

        if (null !== $timeout_watcher->value) {
            $timeout_watcher_value = $timeout_watcher->value;
            $timeout_watcher->value = null;
            Scheduler::cancel($timeout_watcher_value);
        } elseif ($state->isComplete()) {
            // timeout has been reached.
            return;
        }

        if (null !== $exception) {
            $state->error($exception);
        } else {
            $state->complete($result);
        }
    });

    return new Awaitable($state);
}
