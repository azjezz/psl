<?php

declare(strict_types=1);

namespace Psl\UDP\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Revolt\EventLoop;

/**
 * Wait for a stream to become readable, with cancellation support.
 *
 * @internal
 *
 * @param resource $stream
 *
 * @throws CancelledException If the operation is cancelled.
 */
function await_readable(mixed $stream, CancellationTokenInterface $cancellation): void
{
    if ($cancellation->cancellable) {
        $cancellation->throwIfCancelled();
    }

    $suspension = EventLoop::getSuspension();

    $readWatcher = EventLoop::onReadable($stream, static function (string $watcher) use ($suspension): void {
        EventLoop::cancel($watcher);
        $suspension->resume();
    });

    $id = null;
    if ($cancellation->cancellable) {
        $id = $cancellation->subscribe($suspension->throw(...));
    }

    try {
        $suspension->suspend();
    } finally {
        EventLoop::cancel($readWatcher);
        if (null !== $id) {
            $cancellation->unsubscribe($id);
        }
    }
}
