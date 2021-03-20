<?php

declare(strict_types=1);

namespace Psl\Asio;

use Fiber;
use Throwable;

/**
 * Execute the given callable asynchronously.
 *
 * @template T
 *
 * @param (callable(): T) $callable
 *
 * @return Awaitable<T>
 */
function async(callable $callable): Awaitable
{
    /** @var Internal\WaitHandle<T> */
    $placeholder = new Internal\WaitHandle();

    $fiber = new Fiber(static function () use ($placeholder, $callable): void {
        try {
            $placeholder->finish($callable());
        } catch (Throwable $exception) {
            $placeholder->fail($exception);
        }
    });

    Internal\EventLoop::defer(
        static function () use ($fiber): void {
            $fiber->start();
        },
        null
    );

    return new Internal\InternalAwaitable($placeholder);
}
