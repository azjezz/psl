<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Math;

/**
 * Encode a signed 32-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_i32(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < Math\INT32_MIN || $value > Math\INT32_MAX) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for i32 (' . Math\INT32_MIN . '..' . Math\INT32_MAX . ').',
        );
    }

    return pack(match ($endianness) {
        Endianness::Big => 'N',
        Endianness::Little => 'V',
    }, $value & 0xFFFF_FFFF);
}
