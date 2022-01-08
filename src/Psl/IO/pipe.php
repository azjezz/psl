<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Internal;
use Psl\OS;

use function error_get_last;
use function stream_socket_pair;

use const STREAM_IPPROTO_IP;
use const STREAM_PF_INET;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

/**
 * Create a pair of handles, where writes to the {@see WriteStreamHandleInterface} can be read from the {@see ReadStreamHandleInterface}.
 *
 * @return array{0: CloseReadStreamHandleInterface, 1: CloseWriteStreamHandleInterface}
 */
function pipe(): array
{
    $sockets = Internal\suppress(
        /**
         * @return array{0: resource, 1: resource}
         */
        static function (): array {
            $domain = OS\is_windows() ? STREAM_PF_INET : STREAM_PF_UNIX;
            $sockets = stream_socket_pair($domain, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
            // @codeCoverageIgnoreStart
            if ($sockets === false) {
                $error = error_get_last();
                $message = $error['message'] ?? 'Unable to create a pipe stream.';
                Psl\invariant_violation($message);
            }
            // @codeCoverageIgnoreEnd

            return [$sockets[0], $sockets[1]];
        },
    );

    return [
        new CloseReadStreamHandle($sockets[0]),
        new CloseWriteStreamHandle($sockets[1]),
    ];
}
