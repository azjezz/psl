<?php

declare(strict_types=1);

namespace Psl\Binary;

use function strlen;
use function unpack;

/**
 * Decode a signed 16-bit integer from a binary string.
 *
 * @throws Exception\UnderflowException If $bytes has fewer than 2 bytes.
 *
 * @return int<-32768, 32767>
 *
 * @pure
 */
function decode_i16(string $bytes, Endianness $endianness = Endianness::Big): int
{
    if (strlen($bytes) < 2) {
        throw new Exception\UnderflowException('Expected at least 2 bytes, got ' . strlen($bytes) . '.');
    }

    $value = unpack(match ($endianness) {
        Endianness::Big => 'n',
        Endianness::Little => 'v',
    }, $bytes)[1];

    return $value >= 0x8000 ? $value - 0x1_0000 : $value;
}
