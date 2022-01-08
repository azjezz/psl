<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\Network;
use Psl\OS;

/**
 * Connect to a socket.
 *
 * @param non-empty-string $path
 *
 * @throws Network\Exception\RuntimeException If failed to connect to client on the given address.
 * @throws Network\Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 */
function connect(string $path, ?float $timeout = null): Network\StreamSocketInterface
{
    // @codeCoverageIgnoreStart
    if (OS\is_windows()) {
        throw new Network\Exception\RuntimeException('Unix socket is not supported on Windows platform.');
    }
    // @codeCoverageIgnoreEnd

    $socket = Network\Internal\socket_connect("unix://{$path}", timeout: $timeout);

    /** @psalm-suppress MissingThrowsDocblock */
    return new Network\Internal\Socket($socket);
}
