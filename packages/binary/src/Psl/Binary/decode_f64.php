<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode a 64-bit floating point value from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 8 bytes.
 *
 * @pure
 */
function decode_f64(string $bytes, Endianness $endianness = Endianness::Big): float
{
    if (strlen($bytes) < 8) {
        throw new Exception\UnderflowException('Expected at least 8 bytes, got ' . strlen($bytes) . '.');
    }

    return unpack(match ($endianness) {
        Endianness::Big => 'E',
        Endianness::Little => 'e',
    }, $bytes)[1];
}
