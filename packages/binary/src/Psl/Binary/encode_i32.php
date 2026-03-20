<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode a signed 32-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_i32(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < -2_147_483_648 || $value > 2_147_483_647) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for i32 (-2147483648..2147483647).',
        );
    }

    return pack(match ($endianness) {
        Endianness::Big => 'N',
        Endianness::Little => 'V',
    }, $value & 0xFFFF_FFFF);
}
