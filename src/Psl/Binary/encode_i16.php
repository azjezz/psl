<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Math;

/**
 * Encode a signed 16-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_i16(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < Math\INT16_MIN || $value > Math\INT16_MAX) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for i16 (' . Math\INT16_MIN . '..' . Math\INT16_MAX . ').',
        );
    }

    return pack(match ($endianness) {
        Endianness::Big => 'n',
        Endianness::Little => 'v',
    }, $value & 0xFFFF);
}
