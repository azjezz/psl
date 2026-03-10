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
 * @param int<1, max> $idle_connections Maximum number of idle connections to buffer.
 * @param int<1, max> $backlog Maximum length of the queue of pending connections.
 *
 * @throws Network\Exception\RuntimeException If failed to listen on given address.
 */
function listen(
    string $host = '127.0.0.1',
    int $port = 0,
    bool $no_delay = false,
    bool $reuse_address = false,
    bool $reuse_port = false,
    int $idle_connections = 256,
    int $backlog = 512,
): ListenerInterface {
    $socket_context = ['socket' => [
        'ipv6_v6only' => true,
        'so_reuseaddr' => OS\is_windows() ? $reuse_port : $reuse_address,
        'so_reuseport' => $reuse_port,
        'so_broadcast' => false,
        'tcp_nodelay' => $no_delay,
        'backlog' => $backlog,
    ]];

    $socket = Network\Internal\server_listen("tcp://{$host}:{$port}", $socket_context);

    return new Internal\Listener($socket, $idle_connections);
}
