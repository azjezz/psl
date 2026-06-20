<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async;
use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * Copy data bidirectionally between two handles until both sides reach EOF, using a custom chunk size.
 *
 * Data flows from $a to $b and from $b to $a concurrently until both directions
 * reach EOF. Each direction reads in chunks of $chunkSize bytes.
 *
 * @param positive-int $chunkSize Maximum number of bytes to read per iteration in each direction.
 *
 * @return array{int<0, max>, int<0, max>} [bytes_a_to_b, bytes_b_to_a]
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws CancelledException If the operation is cancelled.
 *
 * @api
 */
function copy_bidirectional_chunked(
    ReadHandleInterface&WriteHandleInterface $a,
    ReadHandleInterface&WriteHandleInterface $b,
    int $chunkSize,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): array {
    return Async\concurrently::<int, int>([
        static fn(): int => namespace\copy_chunked($a, $b, $chunkSize, $cancellation),
        static fn(): int => namespace\copy_chunked($b, $a, $chunkSize, $cancellation),
    ]);
}
