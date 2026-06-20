<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Revolt\EventLoop;
use Throwable;

/**
 * Create a new fiber asynchronously using the given closure.
 *
 * @param (Closure(): T) $closure
 *
 * @api
 */
function run<T>(Closure $closure): Awaitable<T>
{
    $state = new Internal\State::<T>();

    EventLoop::defer(static function () use ($closure, $state): void {
        try {
            $result = $closure();

            $state->complete($result);
        } catch (Throwable $exception) {
            $state->error($exception);
        }
    });

    return new Awaitable::<T>($state);
}
