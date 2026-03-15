<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Network;
use Psl\OS;

/**
 * Create a TCP listener bound to the given address.
 *
 * @param non-empty-string $host
 * @param int<0, max> $port
 * @param int<1, max> $idleConnections Maximum number of idle connections to buffer.
 * @param int<1, max> $backlog Maximum length of the queue of pending connections.
 *
 * @throws Network\Exception\RuntimeException If failed to listen on given address.
 */
function listen(
    string $host = '127.0.0.1',
    int $port = 0,
    bool $noDelay = false,
    bool $reuseAddress = false,
    bool $reusePort = false,
    int $idleConnections = 256,
    int $backlog = 512,
): ListenerInterface {
    $socketContext = ['socket' => [
        'ipv6_v6only' => true,
        'so_reuseaddr' => OS\is_windows() ? $reusePort : $reuseAddress,
        'so_reuseport' => $reusePort,
        'so_broadcast' => false,
        'tcp_nodelay' => $noDelay,
        'backlog' => $backlog,
    ]];

    $socket = Network\Internal\server_listen("tcp://{$host}:{$port}", $socketContext);

    return new Internal\Listener($socket, $idleConnections);
}
