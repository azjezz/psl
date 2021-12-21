<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Network;

/**
 * Connect to a socket.
 *
 * @param non-empty-string $host
 * @param int<0, max> $port
 *
 * @throws Network\Exception\RuntimeException If failed to connect to client on the given address.
 * @throws Network\Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 */
function connect(
    string $host,
    int $port = 0,
    ?ConnectOptions $options = null,
    ?float $timeout = null,
): Network\StreamSocketInterface {
    $options ??= ConnectOptions::create();

    $context = [
        'socket' => [
            'tcp_nodelay' => $options->noDelay,
        ]
    ];

    $socket = Network\Internal\socket_connect("tcp://{$host}:{$port}", $context, $timeout);

    /** @psalm-suppress MissingThrowsDocblock */
    return new Network\Internal\Socket($socket);
}
