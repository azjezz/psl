<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode a signed 64-bit integer to a binary string.
 *
 * @pure
 */
function encode_i64(int $value, Endianness $endianness = Endianness::Big): string
{
    return pack(match ($endianness) {
        Endianness::Big => 'J',
        Endianness::Little => 'P',
    }, $value);
}
