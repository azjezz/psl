<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode an unsigned 64-bit integer to a binary string.
 *
 * Note: PHP integers are signed 64-bit, so the maximum representable
 * unsigned value is PHP_INT_MAX (2^63 - 1).
 *
 * @throws Exception\OverflowException If $value is negative.
 *
 * @pure
 */
function encode_u64(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < 0) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for u64 (0..PHP_INT_MAX).');
    }

    return pack(match ($endianness) {
        Endianness::Big => 'J',
        Endianness::Little => 'P',
    }, $value);
}
