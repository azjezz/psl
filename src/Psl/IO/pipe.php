<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;
use Psl\Internal;

use function error_get_last;
use function stream_socket_pair;

use const PHP_OS_FAMILY;
use const STREAM_IPPROTO_IP;
use const STREAM_PF_INET;
use const STREAM_PF_UNIX;
use const STREAM_SOCK_STREAM;

/**
 * Create a pair of handles, where writes to the {@see Stream\WriteHandleInterface} can be read from the {@see Stream\ReadHandleInterface}.
 *
 * @return array{0: Stream\CloseReadHandleInterface, 1: Stream\CloseWriteHandleInterface}
 */
function pipe(): array
{
    $sockets = Internal\suppress(
        /**
         * @return array{0: resource, 1: resource}
         */
        static function (): array {
            $domain = PHP_OS_FAMILY === 'Windows' ? STREAM_PF_INET : STREAM_PF_UNIX;
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
        new Stream\CloseReadHandle($sockets[0]),
        new Stream\CloseWriteHandle($sockets[1]),
    ];
}
