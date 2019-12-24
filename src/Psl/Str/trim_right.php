<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the given string with whitespace stripped from the right.
 *
 * If the optional character mask isn't provided, the following characters will
 * be stripped: space, tab, newline, carriage return, NUL byte, vertical tab.
 */
function trim_right(string $string, ?string $char_mask = null): string
{
    $char_mask = $char_mask ?? " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}";
    $char_mask = preg_quote($char_mask);

    return \preg_replace("{^[$char_mask]++}u", '', $string);
}
