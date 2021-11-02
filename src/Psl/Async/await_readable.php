<?php

declare(strict_types=1);

namespace Psl\Async;

use Revolt\EventLoop;

/**
 * Wait for the given resource to become readable in a non-blocking way.
 *
 * @param resource|object $resource
 *
 * @throws Exception\TimeoutException If $timeout_ms is non-null, and the operation timed-out.
 *
 * @codeCoverageIgnore
 */
function await_readable(mixed $resource, bool $reference = true, ?int $timeout_ms = null): void
{
    $suspension = Scheduler::createSuspension();

    $timeout_watcher = null;
    if (null !== $timeout_ms) {
        $timeout_watcher = Scheduler::delay($timeout_ms, static fn() => $suspension->throw(new Exception\TimeoutException()));
        Scheduler::unreference($timeout_watcher);
    }

    $watcher = EventLoop::onReadable(
        $resource,
        static function (string $_watcher, mixed $resource) use ($suspension, $timeout_watcher): void {
            if (null !== $timeout_watcher) {
                Scheduler::cancel($timeout_watcher);
            }

            $suspension->resume($resource);
        },
    );

    if (!$reference) {
        Scheduler::unreference($watcher);
    }

    try {
        $suspension->suspend();
    } finally {
        Scheduler::cancel($watcher);
    }
}
