<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\DateTime;
use Revolt\EventLoop;

/**
 * Non-blocking sleep for the specified number of seconds.
 */
function sleep(DateTime\Duration|float $seconds): void
{
    if ($seconds instanceof DateTime\Duration) {
        $seconds = $seconds->getTotalSeconds();
    }

    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::delay($seconds, static fn () => $suspension->resume());

    try {
        $suspension->suspend();
    } finally {
        EventLoop::cancel($watcher);
    }
}
