<?php

declare(strict_types=1);

namespace Psl\Async;

use Revolt\EventLoop;
use Revolt\EventLoop\UnsupportedFeatureException;

/**
 * Wait for signal in a non-blocking way.
 *
 * @throws Exception\TimeoutException If $timeout_ms is non-null, and the operation timed-out.
 * @throws UnsupportedFeatureException If signal handling is not supported.
 *
 * @codeCoverageIgnore
 */
function await_signal(int $signal, bool $reference = true, ?int $timeout_ms = null): void
{
    $suspension = Scheduler::createSuspension();

    $watcher = EventLoop::onSignal(
        $signal,
        static fn(string $_watcher, int $signal_number) => $suspension->resume($signal_number)
    );

    if (!$reference) {
        Scheduler::unreference($watcher);
    }

    $timeout_watcher = null;
    if (null !== $timeout_ms) {
        $timeout_watcher = Scheduler::delay($timeout_ms, static fn() => $suspension->throw(new Exception\TimeoutException()));
    }

    try {
        $suspension->suspend();
    } finally {
        Scheduler::cancel($watcher);

        if (null !== $timeout_watcher) {
            Scheduler::cancel($timeout_watcher);
        }
    }
}
