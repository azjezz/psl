<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Internal;
use Psl\Network\Exception;
use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;

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
 * @throws CancelledException If the operation was cancelled.
 *
 * @return resource
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function socket_connect(
    string $uri,
    array $context = [],
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): mixed {
    return Internal\suppress(
        /**
         * @return resource
         */
        static function () use ($uri, $context, $cancellation): mixed {
            $cancellation->throwIfCancelled();

            $_error_message = null;
            $error_code = null;

            $context = stream_context_create($context);
            $socket = @stream_socket_client(
                $uri,
                $error_code,
                $_error_message,
                null,
                STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT,
                $context,
            );

            if (!$socket || $error_code) {
                throw new Exception\RuntimeException('Failed to connect to client "' . $uri . '".', (int) $error_code);
            }

            /** @var Suspension<resource> */
            $suspension = EventLoop::getSuspension();

            $write_watcher = '';
            $cancellation_id = $cancellation->subscribe(static function (CancelledException $exception) use (
                $suspension,
                &$write_watcher,
                $socket,
            ): void {
                EventLoop::cancel($write_watcher);

                if (is_resource($socket)) {
                    fclose($socket);
                }

                $suspension->throw($exception);
            });

            $write_watcher = EventLoop::onWritable($socket, static function () use (
                $suspension,
                $socket,
                $cancellation,
                $cancellation_id,
            ): void {
                $cancellation->unsubscribe($cancellation_id);

                $suspension->resume($socket);
            });

            try {
                return $suspension->suspend();
            } finally {
                EventLoop::cancel($write_watcher);
                $cancellation->unsubscribe($cancellation_id);
            }
        },
    );
}
