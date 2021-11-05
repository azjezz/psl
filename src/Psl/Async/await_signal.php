<?php

declare(strict_types=1);

namespace Psl\Async;

use Revolt\EventLoop;
use Revolt\EventLoop\UnsupportedFeatureException;

/**
 * Wait for signal in a non-blocking way.
 *
 * @throws Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 * @throws UnsupportedFeatureException If signal handling is not supported.
 *
 * @codeCoverageIgnore
 */
function await_signal(int $signal, bool $reference = true, ?float $timeout = null): void
{
    $suspension = Scheduler::createSuspension();

    $timeout_watcher = null;
    if (null !== $timeout) {
        $timeout_watcher = Scheduler::delay($timeout, static fn() => $suspension->throw(new Exception\TimeoutException()));
        Scheduler::unreference($timeout_watcher);
    }

    $watcher = EventLoop::onSignal(
        $signal,
        static fn() => $suspension->resume($signal),
    );

    if (!$reference) {
        Scheduler::unreference($watcher);
    }

    try {
        $suspension->suspend();
    } finally {
        Scheduler::cancel($watcher);

        // cancel timeout watcher
        if (null !== $timeout_watcher) {
            Scheduler::cancel($timeout_watcher);
        }
    }
}
