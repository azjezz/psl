<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Async\Exception\ResourceClosedException;
use Revolt\EventLoop;
use Throwable;

/**
 * Wait for the given resource to become writable in a non-blocking way.
 *
 * @param resource|object $resource
 *
 * @throws Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 * @throws Exception\ResourceClosedException If $resource was closed before it became writable.
 *
 * @codeCoverageIgnore
 */
function await_writable(mixed $resource, bool $reference = true, ?float $timeout = null): void
{
    $suspension = Scheduler::createSuspension();

    $timeout_watcher = null;
    if (null !== $timeout) {
        $timeout_watcher = Scheduler::delay($timeout, static fn() => $suspension->throw(new Exception\TimeoutException()));
        Scheduler::unreference($timeout_watcher);
    }

    $watcher = EventLoop::onWritable(
        $resource,
        static fn() => $suspension->resume($resource),
    );

    if (!$reference) {
        Scheduler::unreference($watcher);
    }

    try {
        $suspension->suspend();
    } catch (Throwable $e) {
        if (!is_resource($resource)) {
            throw new ResourceClosedException('Resource was closed before it became writable.');
        }

        /**
         * @psalm-suppress MissingThrowsDocblock
         * @psalm-suppress PossiblyUndefinedVariable
         */
        throw $e;
    } finally {
        Scheduler::cancel($watcher);

        // cancel timeout watcher
        if (null !== $timeout_watcher) {
            Scheduler::cancel($timeout_watcher);
        }
    }
}
