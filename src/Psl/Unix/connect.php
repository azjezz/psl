<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;
use Psl\Network;

/**
 * Connect to a Unix domain socket.
 *
 * @param non-empty-string $path
 *
 * @throws Network\Exception\RuntimeException If failed to connect on the given path, or if on Windows.
 * @throws CancelledException If the operation is cancelled.
 */
function connect(string $path, CancellationTokenInterface $cancellation = new NullCancellationToken()): StreamInterface
{
    Internal\assert_not_windows();

    $socket = Network\Internal\socket_connect("unix://{$path}", cancellation: $cancellation);

    return new Internal\Stream($socket);
}
