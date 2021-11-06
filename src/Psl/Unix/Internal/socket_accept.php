<?php

declare(strict_types=1);

namespace Psl\Unix\Internal;

use Psl\Async;
use Psl\Network;

use function error_clear_last;
use function error_get_last;
use function stream_socket_accept;

/**
 * @param resource $server
 *
 * @throws Network\Exception\RuntimeException In case failed to accept incoming connection.
 * @throws Network\Exception\AlreadyStoppedException In case the server socket was closed while waiting.
 *
 * @codeCoverageIgnore
 *
 * @return resource
 *
 * @internal
 */
function socket_accept(mixed $server): mixed
{
    $retry = true;
    while (true) {
        error_clear_last();
        $sock = @stream_socket_accept($server, timeout: 0.0); // don't timeout.
        if ($sock !== false) {
            return $sock;
        }

        if ($retry === false) {
            /** @var int $err */
            $err = error_get_last();
            throw new Network\Exception\RuntimeException('Failed to accept incoming connection.', $err);
        }

        try {
            Async\await_readable($server);
        } catch (Async\Exception\ResourceClosedException) {
            throw new Network\Exception\AlreadyStoppedException('Server socket has already been stopped.');
        }

        $retry = false;
    }
}
