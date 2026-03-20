<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode a signed 64-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 8 bytes.
 *
 * @pure
 */
function decode_i64(string $bytes, Endianness $endianness = Endianness::Big): int
{
    if (strlen($bytes) < 8) {
        throw new Exception\UnderflowException('Expected at least 8 bytes, got ' . strlen($bytes) . '.');
    }

    return unpack(match ($endianness) {
        Endianness::Big => 'J',
        Endianness::Little => 'P',
    }, $bytes)[1];
}
