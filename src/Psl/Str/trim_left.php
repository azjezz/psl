<?php

declare(strict_types=1);

namespace Psl\Str;

use function preg_last_error_msg;
use function preg_quote;
use function preg_replace;

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
function trim_left(string $string, null|string $charMask = null): string
{
    $charMask ??= " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}";
    $charMask = preg_quote($charMask, null);

    $result = preg_replace("{^[{$charMask}]++}uD", '', $string);
    if (null === $result) {
        throw new Exception\InvalidArgumentException(preg_last_error_msg());
    }

    return $result;
}
