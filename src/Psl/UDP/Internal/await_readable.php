<?php

declare(strict_types=1);

namespace Psl\UDP\Internal;

use Psl\DateTime\Duration;
use Psl\IO;
use Revolt\EventLoop;

/**
 * Wait for a stream to become readable, with an optional timeout.
 *
 * @internal
 *
 * @param resource $stream
 *
 * @throws IO\Exception\TimeoutException If the timeout expires before the stream becomes readable.
 */
function await_readable(mixed $stream, null|Duration $timeout): void
{
    $suspension = EventLoop::getSuspension();
    $timeout_watcher = null;

    if ($timeout !== null) {
        $timeout_watcher = EventLoop::delay($timeout->getTotalSeconds(), static function () use ($suspension): void {
            $suspension->resume(true);
        });
    }

    $read_watcher = EventLoop::onReadable($stream, static function (string $watcher) use ($suspension): void {
        EventLoop::cancel($watcher);
        $suspension->resume(false);
    });

    /** @var bool $timed_out */
    $timed_out = $suspension->suspend();
    if ($timeout_watcher !== null) {
        EventLoop::cancel($timeout_watcher);
    }

    EventLoop::cancel($read_watcher);

    if ($timed_out) {
        throw new IO\Exception\TimeoutException('UDP receive operation timed out.');
    }
}
