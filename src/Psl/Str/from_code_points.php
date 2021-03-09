<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Create a Multi-byte string from code points.
 *
 * Example:
 *
 *      Str\from_code_points(1605, 1585, 1581, 1576, 1575, 32, 1576, 1603, 1605)
 *      => Str('مرحبا بكم')
 *
 *      Str\from_code_points(72, 101, 108, 108, 111)
 *      => Str('Hello')
 *
 * @pure
 */
function from_code_points(int ...$code_points): string
{
    $string = '';
    foreach ($code_points as $code) {
        if (0x80 > $code %= 0x200000) {
            $string .= Byte\chr($code);
        } elseif (0x800 > $code) {
            $string .= Byte\chr(0xC0 | $code >> 6) . Byte\chr(0x80 | $code & 0x3F);
        } elseif (0x10000 > $code) {
            $string .= Byte\chr(0xE0 | $code >> 12) . Byte\chr(0x80 | $code >> 6 & 0x3F);
            $string .= Byte\chr(0x80 | $code & 0x3F);
        } else {
            $string .= Byte\chr(0xF0 | $code >> 18) . Byte\chr(0x80 | $code >> 12 & 0x3F);
            $string .= Byte\chr(0x80 | $code >> 6 & 0x3F) . Byte\chr(0x80 | $code & 0x3F);
        }
    }

    return $string;
}
