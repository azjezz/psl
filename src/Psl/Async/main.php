<?php

declare(strict_types=1);

namespace Psl\Async;

use Throwable;

/**
 * Execute the given callable in an async context, then exit with returned exit code.
 *
 * If the callable returns an awaitable, the awaitable *MUST* resolve with an exit code.
 *
 * @param (callable(): int)|(callable(): Awaitable<int>) $callable
 *
 * @codeCoverageIgnore
 */
function main(callable $callable): never
{
    $main = Scheduler::createSuspension();

    Scheduler::defer(static function () use ($callable, $main): void {
        try {
            $exit_code = $callable();
            $main->resume($exit_code);
        } catch (Throwable $throwable) {
            $main->throw($throwable);
        }
    });

    /** @var int|Awaitable<int> $return */
    $return = $main->suspend();
    if ($return instanceof Awaitable) {
        $return = $return->await();
    }

    exit($return);
}
