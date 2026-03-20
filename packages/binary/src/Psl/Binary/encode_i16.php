<?php

declare(strict_types=1);

namespace Psl\Binary;

use function pack;

/**
 * Encode a signed 16-bit integer to a binary string.
 *
 * @throws Exception\OverflowException If $value is out of range.
 *
 * @pure
 */
function encode_i16(int $value, Endianness $endianness = Endianness::Big): string
{
    if ($value < -32_768 || $value > 32_767) {
        throw new Exception\OverflowException('Value ' . $value . ' is out of range for i16 (-32768..32767).');
    }

    return pack(match ($endianness) {
        Endianness::Big => 'n',
        Endianness::Little => 'v',
    }, $value & 0xFFFF);
}
