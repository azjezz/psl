<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async;
use Psl\DateTime\Duration;

use function strlen;

/**
 * Copy data from a read handle to a write handle until EOF.
 *
 * Reads from $reader until EOF and writes all data to $writer.
 *
 * @return int<0, max> The total number of bytes copied.
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws Exception\TimeoutException If the operation times out.
 */
function copy(ReadHandleInterface $reader, WriteHandleInterface $writer, null|Duration $timeout = null): int
{
    $timer = new Async\OptionalIncrementalTimeout($timeout, static function (): never {
        throw new Exception\TimeoutException('Copy operation timed out.');
    });

    $bytes_copied = 0;
    $buffer_size = 8192;

    while (true) {
        $data = $reader->read($buffer_size, $timer->getRemaining());
        if ($data === '') {
            if ($reader->reachedEndOfDataSource()) {
                break;
            }

            continue;
        }

        $writer->writeAll($data, $timer->getRemaining());
        $bytes_copied += strlen($data);
    }

    /** @var int<0, max> */
    return $bytes_copied;
}
