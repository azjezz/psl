<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode a signed 8-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_i8(int $value): string
{
    if ($value < -128 || $value > 127) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for i8 (-128..127).');
    }

    return pack('c', $value);
}
