<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\Network;

/**
 * Create a Unix domain socket listener bound to the given path.
 *
 * @param non-empty-string $path
 * @param int<1, max> $idle_connections Maximum number of idle connections to buffer.
 *
 * @throws Network\Exception\RuntimeException If failed to listen on given path, or if on Windows.
 */
function listen(string $path, int $idle_connections = 256): ListenerInterface
{
    Internal\assert_not_windows();

    $socket = Network\Internal\server_listen("unix://{$path}");

    return new Internal\Listener($socket, $idle_connections);
}
