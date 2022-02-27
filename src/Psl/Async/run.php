<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Revolt\EventLoop;
use Throwable;

/**
 * Create a new fiber asynchronously using the given closure.
 *
 * @template T
 *
 * @param (Closure(): T) $closure
 *
 * @return Awaitable<T>
 */
function run(Closure $closure): Awaitable
{
    $state = new Internal\State();

    EventLoop::defer(static function () use ($closure, $state) {
        try {
            $result = $closure();

            $state->complete($result);
        } catch (Throwable $exception) {
            $state->error($exception);
        }
    });

    return new Awaitable($state);
}
