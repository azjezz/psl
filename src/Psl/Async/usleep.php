<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Non-blocking sleep for the specified number of micro-seconds.
 */
function usleep(int $microseconds): void
{
    $suspension = Scheduler::createSuspension();
    $watcher = Scheduler::delay($microseconds, static fn () => $suspension->resume(null));

    try {
        $suspension->suspend();
    } finally {
        Scheduler::cancel($watcher);
    }
}
