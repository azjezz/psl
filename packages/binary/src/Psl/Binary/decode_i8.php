<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode a signed 8-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 1 byte.
 *
 * @return int<-128, 127>
 *
 * @pure
 */
function decode_i8(string $bytes): int
{
    if (strlen($bytes) < 1) {
        throw new Exception\UnderflowException('Expected at least 1 byte, got ' . strlen($bytes) . '.');
    }

    return unpack('c', $bytes)[1];
}
