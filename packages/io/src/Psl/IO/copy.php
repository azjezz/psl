<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * Copy data from a read handle to a write handle until EOF.
 *
 * Reads from $reader in 8 KB chunks until EOF and writes all data to $writer.
 * If the writer implements {@see BufferedWriteHandleInterface}, it is flushed
 * after all data has been written to ensure no bytes remain in an internal buffer.
 *
 * For a custom chunk size, use {@see copy_chunked()}.
 *
 * @return int<0, max> The total number of bytes copied.
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws CancelledException If the operation is cancelled.
 *
 * @api
 */
function copy(
    ReadHandleInterface $reader,
    WriteHandleInterface $writer,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): int {
    return namespace\copy_chunked($reader, $writer, 8192, $cancellation);
}
