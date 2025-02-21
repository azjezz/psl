<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\DateTime;
use Revolt\EventLoop;

/**
 * Non-blocking sleep for the specified number of seconds.
 */
function sleep(DateTime\Duration $duration): void
{
    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::delay($duration->getTotalSeconds(), static fn(): null => $suspension->resume());

    try {
        $suspension->suspend();
    } finally {
        EventLoop::cancel($watcher);
    }
}
