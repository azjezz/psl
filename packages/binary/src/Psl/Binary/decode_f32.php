<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode a 32-bit floating point value from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 4 bytes.
 *
 * @pure
 */
function decode_f32(string $bytes, Endianness $endianness = Endianness::Big): float
{
    if (strlen($bytes) < 4) {
        throw new Exception\UnderflowException('Expected at least 4 bytes, got ' . strlen($bytes) . '.');
    }

    return unpack(match ($endianness) {
        Endianness::Big => 'G',
        Endianness::Little => 'g',
    }, $bytes)[1];
}
