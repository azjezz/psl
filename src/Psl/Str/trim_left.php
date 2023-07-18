<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl\Regex;

use function preg_quote;

/**
 * Returns the given string with whitespace stripped from the left.
 *
 * If the optional character mask isn't provided, the following characters will
 * be stripped: space, tab, newline, carriage return, NUL byte, vertical tab.
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException if $string is not a valid UTF-8 string.
 */
function trim_left(string $string, ?string $char_mask = null): string
{
    $char_mask ??= " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}";
    $char_mask   = preg_quote($char_mask, null);

    try {
        return Regex\replace($string, "{^[{$char_mask}]++}uD", '');
    } catch (Regex\Exception\RuntimeException | Regex\Exception\InvalidPatternException $error) {
        throw new Exception\InvalidArgumentException($error->getMessage(), previous: $error);
    }
}
