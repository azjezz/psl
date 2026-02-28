<?php

declare(strict_types=1);

namespace Psl\UDP\Internal;

use Psl\DateTime\Duration;
use Psl\IO;
use Revolt\EventLoop;

/**
 * Wait for a stream to become writable, with a timeout.
 *
 * @internal
 *
 * @param resource $stream
 *
 * @throws IO\Exception\TimeoutException If the timeout expires before the stream becomes writable.
 */
function wait_writable(mixed $stream, Duration $timeout): void
{
    $suspension = EventLoop::getSuspension();
    $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use ($suspension): void {
        $suspension->resume(true);
    });

    $write_watcher = EventLoop::onWritable($stream, static function (string $watcher) use ($suspension): void {
        EventLoop::cancel($watcher);
        $suspension->resume(false);
    });

    /** @var bool $timed_out */
    $timed_out = $suspension->suspend();
    EventLoop::cancel($timeout_watcher);
    EventLoop::cancel($write_watcher);

    if ($timed_out) {
        throw new IO\Exception\TimeoutException('UDP send operation timed out.');
    }
}
