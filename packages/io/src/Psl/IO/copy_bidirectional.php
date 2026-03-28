<?php

declare(strict_types=1);

namespace Psl\IO;

use Psl\Async\CancellationTokenInterface;
use Psl\Async\Exception\CancelledException;
use Psl\Async\NullCancellationToken;

/**
 * Copy data bidirectionally between two handles until both sides reach EOF.
 *
 * Data flows from $a to $b and from $b to $a concurrently using 8 KB chunks
 * until both directions reach EOF. This is useful for building proxies.
 *
 * For a custom chunk size, use {@see copy_bidirectional_chunked()}.
 *
 * @return array{int<0, max>, int<0, max>} [bytes_a_to_b, bytes_b_to_a]
 *
 * @throws Exception\RuntimeException If a read or write error occurs.
 * @throws CancelledException If the operation is cancelled.
 */
function copy_bidirectional(
    ReadHandleInterface&WriteHandleInterface $a,
    ReadHandleInterface&WriteHandleInterface $b,
    CancellationTokenInterface $cancellation = new NullCancellationToken(),
): array {
    return namespace\copy_bidirectional_chunked($a, $b, 8192, $cancellation);
}
