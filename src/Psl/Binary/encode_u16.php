<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Math;

/**
 * Encode an unsigned 16-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_u16(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < 0 || $value > Math\UINT16_MAX) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for u16 (0..' . Math\UINT16_MAX . ').',
        );
    }

    return pack(match ($endianness) {
        Endianness::Big => 'n',
        Endianness::Little => 'v',
    }, $value);
}
