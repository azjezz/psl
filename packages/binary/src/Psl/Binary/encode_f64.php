<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode a 64-bit floating point value to a binary string.
 *
 * @pure
 */
function encode_f64(float $value, Endianness $endianness = Endianness::Big): string
{
    return pack(match ($endianness) {
        Endianness::Big => 'E',
        Endianness::Little => 'e',
    }, $value);
}
