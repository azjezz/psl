<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

use function strlen;

/**
 * Copy data from a read handle to a write handle until EOF, using a custom chunk size.
 *
 * Reads from $reader in chunks of $chunkSize bytes and writes all data to $writer.
 * If the writer implements {@see BufferedWriteHandleInterface}, it is flushed after
 * all data has been written to ensure no bytes remain in an internal buffer.
 *
 * @param positive-int $chunkSize Maximum number of bytes to read per iteration.
 *
 * @return int<0, max> The total number of bytes copied.
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws CancelledException If the operation is cancelled.
 */
function copy_chunked(
    ReadHandleInterface $reader,
    WriteHandleInterface $writer,
    int $chunkSize,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): int {
    $bytesCopied = 0;

    while (true) {
        $data = $reader->read($chunkSize, $cancellation);
        if ($data === '') {
            if ($reader->reachedEndOfDataSource()) {
                break;
            }

            continue;
        }

        $writer->writeAll($data, $cancellation);
        $bytesCopied += strlen($data);
    }

    if ($writer instanceof BufferedWriteHandleInterface) {
        $writer->flush($cancellation);
    }

    return $bytesCopied;
}
