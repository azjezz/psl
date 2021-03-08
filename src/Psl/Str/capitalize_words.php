<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Exception;
use Psl\Internal;

use function mb_convert_case;

use const MB_CASE_TITLE;

/**
 * Returns the string with all words capitalized.
 *
 * Words are delimited by space, tab, newline, carriage return, form-feed, and
 * vertical tab by default, but you can specify custom delimiters.
 *
 * Example:
 *
 *      Str\capitalize_words('hello, world!')
 *      => Str('Hello, World!')
 *
 *      Str\capitalize_words('Hello, world!')
 *      => Str('Hello, World!')
 *
 *      Str\capitalize_words('مرحبا بكم')
 *      => Str('مرحبا بكم')
 *
 * @pure
 *
 * @throws Exception\InvariantViolationException If an invalid $encoding is provided.
 */
function capitalize_words(string $string, ?string $encoding = null): string
{
    return mb_convert_case($string, MB_CASE_TITLE, Internal\internal_encoding($encoding));
}
