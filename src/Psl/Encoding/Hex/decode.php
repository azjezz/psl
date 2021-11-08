<?php

declare(strict_types=1);

namespace Psl\Encoding\Hex;

use Psl\Encoding\Exception;
use Psl\Str;

/**
 * Convert a hexadecimal string into a binary string.
 *
 * Hex ( Base16 ) character set:
 *  [0-9]      [a-f]      [A-F]
 *  0x30-0x39, 0x61-0x66, 0x41-0x46
 *
 * @pure
 *
 * @throws Exception\RangeException If the hexadecimal string contains characters outside the base16 range,
 *                                  or an odd number of characters.
 */
function decode(string $hexadecimal): string
{
    if (!ctype_xdigit($hexadecimal)) {
        throw new Exception\RangeException(
            'The given hexadecimal string contains characters outside the base16 range.'
        );
    }

    $hex_len = Str\length($hexadecimal, Str\Encoding::ASCII_8BIT);
    if (($hex_len & 1) !== 0) {
        throw new Exception\RangeException(
            'Expected an even number of hexadecimal characters.',
        );
    }

    return hex2bin($hexadecimal);
}
