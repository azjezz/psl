<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode an unsigned 8-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_u8(int $value): string
{
    if ($value < 0 || $value > 255) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for u8 (0..255).');
    }

    return pack('C', $value);
}
