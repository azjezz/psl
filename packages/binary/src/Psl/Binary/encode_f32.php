<?php

declare(strict_types=1);

namespace Psl\Binary;

use function is_finite;
use function pack;

/**
 * Encode a 32-bit floating point value to a binary string.
 *
 * Special values (NAN, INF, -INF) are allowed. Finite values must be
 * within the float32 range.
 *
 * @throws Exception\OverflowException If the finite $value exceeds float32 range.
 *
 * @pure
 */
function encode_f32(float $value, Endianness $endianness = Endianness::Big): string
{
    if (is_finite($value) && ($value < -3.402_823_47E+38 || $value > 3.402_823_47E+38)) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for f32.');
    }

    return pack(match ($endianness) {
        Endianness::Big => 'G',
        Endianness::Little => 'g',
    }, $value);
}
