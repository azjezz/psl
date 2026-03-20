<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode an unsigned 16-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_u16(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < 0 || $value > 65_535) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for u16 (0..65535).');
    }

    return pack(match ($endianness) {
        Endianness::Big => 'n',
        Endianness::Little => 'v',
    }, $value);
}
