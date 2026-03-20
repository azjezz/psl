<?php

declare(strict_types=1);

namespace Psl\Str;

use function mb_chr;

/**
 * Return the character for a given Unicode code point.
 *
 * Example:
 *
 *      Str\chr(72)
 *      => Str('H')
 *
 *      Str\chr(1604)
 *      => Str('ل')
 *
 * @throws Exception\OutOfBoundsException If the code point is out of the valid Unicode range.
 *
 * @pure
 */
function chr(int $codepoint, Encoding $encoding = Encoding::Utf8): string
{
    $result = mb_chr($codepoint, $encoding->value);
    if (false === $result) {
        throw new Exception\OutOfBoundsException('Code point ' . $codepoint . ' is not a valid Unicode code point.');
    }

    return $result;
}
