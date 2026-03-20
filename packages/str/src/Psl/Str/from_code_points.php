<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Create a UTF-8 string from Unicode code points.
 *
 * @see chr()
 *
 * Example:
 *
 *      Str\from_code_points(1605, 1585, 1581, 1576, 1575, 32, 1576, 1603, 1605)
 *      => Str('مرحبا بكم')
 *
 *      Str\from_code_points(72, 101, 108, 108, 111)
 *      => Str('Hello')
 *
 * @throws Exception\OutOfBoundsException If a code point is out of the valid Unicode range.
 *
 * @pure
 */
function from_code_points(int ...$codePoints): string
{
    $string = '';
    foreach ($codePoints as $code) {
        $string .= namespace\chr($code);
    }

    return $string;
}
