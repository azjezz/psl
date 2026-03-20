<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode an unsigned 32-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_u32(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < 0 || $value > 4_294_967_295) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for u32 (0..4294967295).');
    }

    return pack(match ($endianness) {
        Endianness::Big => 'N',
        Endianness::Little => 'V',
    }, $value);
}
