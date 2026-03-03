<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Math;

/**
 * Encode an unsigned 32-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_u32(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < 0 || $value > Math\UINT32_MAX) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for u32 (0..' . Math\UINT32_MAX . ').',
        );
    }

    return pack(match ($endianness) {
        Endianness::Big => 'N',
        Endianness::Little => 'V',
    }, $value);
}
