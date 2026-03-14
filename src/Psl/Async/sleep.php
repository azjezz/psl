<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\DateTime;
use Revolt\EventLoop;

/**
 * Non-blocking sleep for the specified duration.
 *
 * If a cancellation token is provided, the sleep can be interrupted early.
 *
 * @throws Exception\CancelledException If the cancellation token is cancelled during the sleep.
 */
function sleep(
    DateTime\Duration $duration,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): void {
    $cancellation->throwIfCancelled();

    $suspension = EventLoop::getSuspension();
    $watcher = EventLoop::delay($duration->getTotalSeconds(), $suspension->resume(...));

    $id = $cancellation->subscribe(static function (Exception\CancelledException $e) use ($suspension, $watcher): void {
        EventLoop::cancel($watcher);
        $suspension->throw($e);
    });

    try {
        $suspension->suspend();
    } finally {
        EventLoop::cancel($watcher);
        $cancellation->unsubscribe($id);
    }
}
