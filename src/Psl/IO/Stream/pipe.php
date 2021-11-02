<?php

declare(strict_types=1);

namespace Psl\IO\Stream;

use Psl;
use Psl\Internal;
use Psl\IO;

use function error_get_last;
use function stream_socket_pair;

use const STREAM_IPPROTO_IP;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

/**
 * Create a pair of handles, where writes to the WriteHandle can be read from the ReadHandle.
 *
 * @throws IO\Exception\BlockingException If unable to set one of the handles to non-blocking mode.
 *
 * @return array{0: StreamCloseReadHandle, 1: StreamCloseWriteHandle}
 */
function pipe(): array
{
    $sockets = Internal\suppress(
        /**
         * @return array{0: resource, 1: resource}
         */
        static function (): array {
            $sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
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
        new StreamCloseReadHandle($sockets[0]),
        new StreamCloseWriteHandle($sockets[1]),
    ];
}
