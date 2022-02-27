<?php

declare(strict_types=1);

namespace Psl\Async;

use Revolt\EventLoop;

/**
 * Non-blocking sleep for the specified number of seconds.
 */
function sleep(float $seconds): void
{
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::delay($seconds, static fn () => $suspension->resume());

    try {
        $suspension->suspend();
    } finally {
        EventLoop::cancel($watcher);
    }
}
