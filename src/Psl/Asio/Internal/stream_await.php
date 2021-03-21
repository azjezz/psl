<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Psl\Asio;
use Throwable;

/**
 * @param resource $resource
 *
 * @return Asio\Awaitable<int>
 */
function stream_await(
    $resource,
    int $flags,
    ?int $timeout = null
): Asio\Awaitable {
    $watcher = null;
    $operation = Asio\async(static function () use ($resource, $flags, &$watcher): int {
        /** @var Deferred<int> $deferred */
        $deferred = new Deferred();
        /** @var Asio\Awaitable<int> $awaitable */
        $awaitable = $deferred->awaitable();

        $callback = static function () use (&$deferred, &$watcher): void {
            if (!$deferred) {
                return;
            }

            /** @var Deferred<int> $deferred */
            $deferred->finish(STREAM_AWAIT_READY);

            $deferred = null;
            if ($watcher) {
                EventLoop::cancel((string) $watcher);
            }

            $watcher = null;
        };

        if ($flags & STREAM_AWAIT_READ) {
            $watcher = EventLoop::onReadable($resource, $callback, null);
        } else {
            $watcher = EventLoop::onWritable($resource, $callback, null);
        }

        return Asio\await($awaitable);
    });

    if (null === $timeout) {
        return $operation;
    }

    return Asio\async(static function () use ($operation, $timeout, &$watcher): int {
        try {
            /** @var int */
            return Asio\await(Asio\timeout($operation, $timeout));
        } catch (Asio\Exception\TimeoutException $e) {
            if ($watcher) {
                EventLoop::cancel((string) $watcher);
                $watcher = null;
            }
            return STREAM_AWAIT_TIMEOUT;
        } catch (Throwable $e) {
            if ($watcher) {
                EventLoop::cancel((string) $watcher);
                $watcher = null;
            }

            return STREAM_AWAIT_ERROR;
        }
    });
}
