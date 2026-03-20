<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode a signed 32-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 4 bytes.
 *
 * @return int<-2147483648, 2147483647>
 *
 * @pure
 */
function decode_i32(string $bytes, Endianness $endianness = Endianness::Big): int
{
    if (strlen($bytes) < 4) {
        throw new Exception\UnderflowException('Expected at least 4 bytes, got ' . strlen($bytes) . '.');
    }

    $value = unpack(match ($endianness) {
        Endianness::Big => 'N',
        Endianness::Little => 'V',
    }, $bytes)[1];

    return $value >= 0x8000_0000 ? $value - 0x1_0000_0000 : $value;
}
