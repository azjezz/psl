<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Execute the given callable in an async context, then exit with returned exit code.
 *
 * If the callable returns an awaitable, the awaitable *MUST* resolve with an exit code.
 *
 * @param (callable(): int)|(callable(): Awaitable<int>)|(callable(): never)|(callable(): Awaitable<never>) $callable
 *
 * @codeCoverageIgnore
 */
function main(callable $callable): never
{
    later();

    $result = $callable();
    if ($result instanceof Awaitable) {
        $result = $result->await();
    }

    exit($result);
}
