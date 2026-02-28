<?php

declare(strict_types=1);

namespace Psl\Unix;

use Psl\DateTime\Duration;
use Psl\Network;

/**
 * Connect to a Unix domain socket.
 *
 * @param non-empty-string $path
 *
 * @throws Network\Exception\RuntimeException If failed to connect on the given path, or if on Windows.
 * @throws Network\Exception\TimeoutException If $timeout is non-null, and the operation timed-out.
 */
function connect(string $path, null|Duration $timeout = null): StreamInterface
{
    Internal\assert_not_windows();

    $socket = Network\Internal\socket_connect("unix://{$path}", timeout: $timeout);

    return new Internal\Stream($socket);
}
