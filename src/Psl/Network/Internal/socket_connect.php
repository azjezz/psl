<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl\Async;
use Psl\Internal;
use Psl\Network\Exception;

use function fclose;
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
        /** @psalm-suppress NullArgument */
        $socket = @stream_socket_client($uri, $errno, $_, null, STREAM_CLIENT_CONNECT | STREAM_CLIENT_ASYNC_CONNECT, $context);
        if (!$socket || $errno) {
            throw new Exception\RuntimeException('Failed to connect to client "' . $uri . '".', $errno);
        }

        try {
            Async\await_writable($socket, timeout: $timeout);
        } catch (Async\Exception\TimeoutException | Async\Exception\ResourceClosedException $e) {
            fclose($socket);

            throw new Exception\TimeoutException('Connection to socket timed out.', 0, $e);
        }

        return $socket;
    });
}
