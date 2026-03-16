<?php

declare(strict_types=1);

namespace Psl\Network;

use Psl\Internal;
use Psl\OS;
use Psl\TCP;

use function error_get_last;
use function stream_socket_pair;

use const STREAM_IPPROTO_IP;
use const STREAM_PF_INET;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

/**
 * Create a pair of connected bidirectional stream sockets.
 *
 * Both ends can read and write. Data written to one end can be read from the other.
 * Useful for testing, inter-fiber communication, or in-process proxying.
 *
 * @return array{StreamInterface, StreamInterface}
 *
 * @throws Exception\RuntimeException If unable to create the socket pair.
 */
function socket_pair(): array
{
    $sockets = Internal\suppress(
        /**
         * @return array{0: resource, 1: resource}
         */
        static function (): array {
            $domain = OS\is_windows() ? STREAM_PF_INET : STREAM_PF_UNIX;
            $sockets = stream_socket_pair($domain, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            // @codeCoverageIgnoreStart
            if (false === $sockets) {
                /** @var array{message?: string} $error */
                $error = error_get_last();
                throw new Exception\RuntimeException($error['message'] ?? 'Failed to create socket pair.');
            }

            // @codeCoverageIgnoreEnd

            return [$sockets[0], $sockets[1]];
        },
    );

    // @codeCoverageIgnoreStart
    if (OS\is_windows()) {
        return [
            new TCP\Internal\Stream($sockets[0]),
            new TCP\Internal\Stream($sockets[1]),
        ];
    }

    // @codeCoverageIgnoreEnd

    $local = Address::unix('<anonymous>');
    return [
        new TCP\Internal\Stream($sockets[0], $local, $local),
        new TCP\Internal\Stream($sockets[1], $local, $local),
    ];
}
