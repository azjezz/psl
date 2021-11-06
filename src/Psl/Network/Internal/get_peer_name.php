<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl\Network;

use function error_clear_last;
use function stream_socket_get_name;
use function strrpos;
use function substr;

/**
 * @param resource $socket
 *
 * @throws Network\Exception\RuntimeException If unable to retrieve peer address.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function get_peer_name(mixed $socket): Network\Address
{
    error_clear_last();
    $result = stream_socket_get_name($socket, true);
    if ($result !== false && $result !== "\0") {
        $separator_position = strrpos($result, ':');
        if (false === $separator_position) {
            return Network\Address::unix($result);
        }

        $scheme = Network\SocketScheme::TCP;
        $host = substr($result, 0, $separator_position);
        $port = (int) substr($result, $separator_position + 1);

        return Network\Address::create($scheme, $host, $port);
    }

    return get_sock_name($socket);
}
