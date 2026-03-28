<?php

declare(strict_types=1);

namespace Psl\TCP;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;

/**
 * Connect to a TCP socket.
 *
 * @param non-empty-string $host
 * @param int<0, max> $port
 *
 * @throws Network\Exception\RuntimeException If failed to connect to client on the given address.
 * @throws CancelledException If the operation was cancelled.
 *
 * @api
 */
function connect(
    string $host,
    int $port,
    ConnectConfiguration $configuration = new ConnectConfiguration(),
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): StreamInterface {
    $socketOptions = [
        'tcp_nodelay' => $configuration->noDelay,
    ];

    if ($configuration->bindTo !== null) {
        $socketOptions['bindto'] = $configuration->bindTo;
    }

    $context = ['socket' => $socketOptions];

    $socket = Network\Internal\socket_connect("tcp://{$host}:{$port}", $context, $cancellation);

    return new Internal\Stream($socket);
}
