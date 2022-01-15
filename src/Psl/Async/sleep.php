<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Non-blocking sleep for the specified number of seconds.
 */
function sleep(float $seconds): void
{
    $suspension = Scheduler::getSuspension();
    $watcher = Scheduler::delay($seconds, $suspension->resume(...));

    try {
        $suspension->suspend();
    } finally {
        Scheduler::cancel($watcher);
    }
}
