<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl\Internal;
use Psl\Network\Exception;
use Revolt\EventLoop;

use function fclose;
use function is_resource;
use function stream_context_create;
use function stream_socket_client;

use const STREAM_CLIENT_ASYNC_CONNECT;
use const STREAM_CLIENT_CONNECT;

/**
 * @param non-empty-string $uri
 *
 * @throws Exception\RuntimeException If failed to connect to client on the given address.
 * @throws Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 *
 * @return resource
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function socket_connect(string $uri, array $context = [], ?float $timeout = null): mixed
{
    return Internal\suppress(static function () use ($uri, $context, $timeout): mixed {
        $context = stream_context_create($context);
        $socket = @stream_socket_client($uri, $errno, $_, null, STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT, $context);
        if (!$socket || $errno) {
            throw new Exception\RuntimeException('Failed to connect to client "' . $uri . '".', $errno);
        }

        $suspension = EventLoop::getSuspension();

        $write_watcher = '';
        $timeout_watcher = '';
        if (null !== $timeout) {
            $timeout_watcher = EventLoop::delay($timeout, static function () use ($suspension, &$write_watcher, $socket) {
                EventLoop::cancel($write_watcher);

                /** @psalm-suppress RedundantCondition - it can be resource|closed-resource */
                if (is_resource($socket)) {
                    fclose($socket);
                }

                $suspension->throw(new Exception\TimeoutException('Connection to socket timed out.'));
            });
        }

        $write_watcher = EventLoop::onWritable($socket, static function () use ($suspension, $socket, $timeout_watcher) {
            EventLoop::cancel($timeout_watcher);

            $suspension->resume($socket);
        });

        try {
            /** @var resource */
            return $suspension->suspend();
        } finally {
            EventLoop::cancel($write_watcher);
            EventLoop::cancel($timeout_watcher);
        }
    });
}
