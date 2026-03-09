<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Math;

/**
 * Encode an unsigned 8-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_u8(int $value): string
{
    if ($value < 0 || $value > Math\UINT8_MAX) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for u8 (0..' . Math\UINT8_MAX . ').',
        );
    }

    return pack('C', $value);
}
