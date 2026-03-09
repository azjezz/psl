<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Str\Byte;

/**
 * Decode a signed 64-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 8 bytes.
 *
 * @pure
 */
function decode_i64(string $bytes, Endianness $endianness = Endianness::Big): int
{
    if (Byte\length($bytes) < 8) {
        throw new Exception\UnderflowException('Expected at least 8 bytes, got ' . Byte\length($bytes) . '.');
    }

    return unpack(match ($endianness) {
        Endianness::Big => 'J',
        Endianness::Little => 'P',
    }, $bytes)[1];
}
