<?php

declare(strict_types=1);

namespace Psl\Network\Internal;

use Psl\Network;

use function error_clear_last;
use function error_get_last;
use function stream_socket_get_name;
use function strrpos;
use function substr;

/**
 * @param resource $socket
 *
 * @throws Network\Exception\RuntimeException If unable to retrieve local address.
 *
 * @internal
 *
 * @codeCoverageIgnore
 */
function get_sock_name(mixed $socket): Network\Address
{
    error_clear_last();
    $result = stream_socket_get_name($socket, false);
    if ($result !== false) {
        $separator_position = strrpos($result, ':');
        if (false === $separator_position) {
            return Network\Address::unix($result);
        }

        $host = substr($result, 0, $separator_position);
        $port = (int) substr($result, $separator_position + 1);

        return Network\Address::tcp($host, $port);
    }

    $err = error_get_last();
    if (null === $err) {
        $code = 0;
    } else {
        $code = $err['type'];
    }

    throw new Network\Exception\RuntimeException('Error retrieving local address.', $code);
}
