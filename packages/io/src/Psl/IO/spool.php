<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl;

use function error_get_last;
use function fopen;

/**
 * Create a handle that writes to memory until a threshold is reached,
 * then transparently spools to a temporary file on disk.
 *
 * @param int<0, max> $maxMemory The maximum number of bytes to keep in memory (default 2MB).
 */
function spool(int $maxMemory = 2_097_152): CloseSeekReadWriteStreamHandle
{
    $stream = Internal\suppress(
        /**
         * @return resource
         */
        static function () use ($maxMemory): mixed {
            $stream = fopen("php://temp/maxmemory:{$maxMemory}", 'w+b');
            // @codeCoverageIgnoreStart
            if ($stream === false) {
                $error = error_get_last();
                $message = $error['message'] ?? 'Unable to create a temporary stream.';
                Psl\invariant_violation($message);
            }

            // @codeCoverageIgnoreEnd

            return $stream;
        },
    );

    return new CloseSeekReadWriteStreamHandle($stream);
}
