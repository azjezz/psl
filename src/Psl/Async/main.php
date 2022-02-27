<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Revolt\EventLoop;

/**
 * Execute the given closure in an async context, then exit with returned exit code.
 *
 * If the closure returns an awaitable, the awaitable *MUST* resolve with an exit code.
 *
 * @param (Closure(): int)|(Closure(): Awaitable<int>)|(Closure(): never)|(Closure(): Awaitable<never>) $closure
 *
 * @codeCoverageIgnore
 */
function main(Closure $closure): never
{
    $result = $closure();
    if ($result instanceof Awaitable) {
        $result = $result->await();
    }

    EventLoop::run();

    exit($result);
}
