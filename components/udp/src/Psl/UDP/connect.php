<?php

declare(strict_types=1);

namespace Psl\UDP;

use Psl\Network;

/**
 * Create a connected UDP socket to the given host and port.
 *
 * This is a convenience function that binds a local socket and connects it in one step.
 *
 * @param non-empty-string $host
 * @param int<0, 65535> $port
 *
 * @throws Network\Exception\RuntimeException If binding or connecting fails.
 */
function connect(string $host, int $port): ConnectedSocket
{
    $socket = Socket::bind();

    return $socket->connect($host, $port);
}
