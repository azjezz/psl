<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\DateTime\Duration;
use Psl\Network;

/**
 * Connect to a TCP socket.
 *
 * @param non-empty-string $host
 * @param int<0, max> $port
 *
 * @throws Network\Exception\RuntimeException If failed to connect to client on the given address.
 * @throws Network\Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 */
function connect(string $host, int $port, bool $no_delay = false, null|Duration $timeout = null): StreamInterface
{
    $context = ['socket' => [
        'tcp_nodelay' => $no_delay,
    ]];

    $socket = Network\Internal\socket_connect("tcp://{$host}:{$port}", $context, $timeout);

    return new Internal\Stream($socket);
}
