<?php

declare(strict_types=1);

namespace Psl\Asio;

/**
 * Creates an artificial timeout for any wait handle.
 *
 * If the timeout expires before the awaitable is finished, the returned awaitable fails with an instance of
 * `Psl\Asio\Exception\TimeoutException`.
 *
 * @template T
 *
 * @param Awaitable<T> $awaitable Wait handle to which the timeout is applied.
 * @param int $timeout Timeout in milliseconds.
 *
 * @return Awaitable<T>
 */
function timeout(Awaitable $awaitable, int $timeout): Awaitable
{
    /** @var Internal\Deferred<T> */
    $deferred = new Internal\Deferred();

    $watcher = Internal\EventLoop::delay($timeout, static function () use (&$deferred) {
        if (!$deferred) {
            return;
        }

        /** @var Internal\Deferred<T> $temp */
        $temp = $deferred; // prevent double resolve
        $deferred = null;
        $temp->fail(new Exception\TimeoutException());
    }, null);

    Internal\EventLoop::unreference($watcher);

    $awaitable->onJoin(static function () use (&$deferred, $awaitable, $watcher) {
        if ($deferred !== null) {
            Internal\EventLoop::cancel($watcher);
            /** @var Internal\Deferred<T> $deferred */
            $deferred->finish($awaitable);
        }
    });

    /** @var Internal\Deferred<T> $deferred */
    return $deferred->awaitable();
}
