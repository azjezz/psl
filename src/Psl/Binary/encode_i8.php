<?php

declare(strict_types=1);

namespace Psl\Binary;

use Psl\Math;

/**
 * Encode a signed 8-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_i8(int $value): string
{
    if ($value < Math\INT8_MIN || $value > Math\INT8_MAX) {
        throw new Exception\OverflowException(
            'Value ' . $value . ' is out of range for i8 (' . Math\INT8_MIN . '..' . Math\INT8_MAX . ').',
        );
    }

    return pack('c', $value);
}
