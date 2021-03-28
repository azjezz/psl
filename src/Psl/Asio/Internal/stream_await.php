<?php

declare(strict_types=1);

namespace Psl\Asio\Internal;

use Amp;
use Psl\Asio;
use Psl\Asio\Awaitable;
use Throwable;

/**
 * @param resource $resource
 *
 * @return Awaitable<int>
 *
 * @psalm-suppress MissingThrowsDocblock
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function stream_await(
    $resource,
    int $flags,
    ?int $timeout = null
): Awaitable {
    $watcher = null;
    $operation = Amp\async(static function () use ($resource, $flags, &$watcher): int {
        /** @var Amp\Deferred<int> $deferred */
        $deferred = new Amp\Deferred();
        $awaitable = $deferred->promise();

        $callback = static function () use (&$deferred, &$watcher): void {
            if (!$deferred) {
                return;
            }

            /** @var Amp\Deferred<int> $deferred */
            $deferred->resolve(STREAM_AWAIT_READY);

            $deferred = null;
            if ($watcher) {
                Amp\Loop::cancel((string) $watcher);
            }

            $watcher = null;
        };

        if ($flags & STREAM_AWAIT_READ) {
            $watcher = Amp\Loop::onReadable($resource, $callback, null);
        } else {
            $watcher = Amp\Loop::onWritable($resource, $callback, null);
        }

        /** @var int */
        return Amp\await($awaitable);
    });

    if (null === $timeout) {
        return new AwaitablePromise($operation);
    }

    return Asio\async(static function () use ($operation, $timeout, &$watcher): int {
        try {
            /** @var int */
            return Amp\await(Amp\Promise\timeout($operation, $timeout));
        } catch (Amp\TimeoutException $e) {
            if ($watcher) {
                Amp\Loop::cancel((string) $watcher);
                $watcher = null;
            }

            return STREAM_AWAIT_TIMEOUT;
        } catch (Throwable $e) {
            if ($watcher) {
                Amp\Loop::cancel((string) $watcher);
                $watcher = null;
            }

            return STREAM_AWAIT_ERROR;
        }
    });
}
