<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

use function strlen;

/**
 * Copy data from a read handle to a write handle until EOF.
 *
 * Reads from $reader until EOF and writes all data to $writer.
 *
 * @return int<0, max> The total number of bytes copied.
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws CancelledException If the operation is cancelled.
 */
function copy(
    ReadHandleInterface $reader,
    WriteHandleInterface $writer,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): int {
    $bytesCopied = 0;
    $bufferSize = 8192;

    while (true) {
        $data = $reader->read($bufferSize, $cancellation);
        if ($data === '') {
            if ($reader->reachedEndOfDataSource()) {
                break;
            }

            continue;
        }

        $writer->writeAll($data, $cancellation);
        $bytesCopied += strlen($data);
    }

    /** @var int<0, max> */
    return $bytesCopied;
}
