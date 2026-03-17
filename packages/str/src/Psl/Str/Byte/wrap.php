<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function wordwrap;

/**
 * Wraps a string to a given number of characters.
 *
 * @param string $break the line is broken using the optional break parameter
 * @param bool $cut If the cut is set to true, the string is always wrapped at
 *                  or before the specified width. So if you have a word that is larger than
 *                  the given width, it is broken apart.
 *
 * @return string the given string wrapped at the specified column
 *
 * @pure
 */
function wrap(string $string, int $width = 75, string $break = "\n", bool $cut = false): string
{
    return wordwrap($string, $width, $break, $cut);
}
