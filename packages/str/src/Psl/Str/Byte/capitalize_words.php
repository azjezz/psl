<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function ucwords;

/**
 * Returns the string with all words capitalized.
 *
 * Words are delimited by space, tab, newline, carriage return, form-feed, and
 * vertical tab by default, but you can specify custom delimiters.
 *
 * @pure
 */
function capitalize_words(string $string, string $delimiters = " \t\r\n\f\v"): string
{
    return ucwords($string, $delimiters);
}
