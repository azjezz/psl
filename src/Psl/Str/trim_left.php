<?php

declare(strict_types=1);

namespace Psl\Str;

use Psl;

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
 * @throws Psl\Exception\InvariantViolationException if $string is not a valid UTF-8 string.
 */
function trim_left(string $string, ?string $char_mask = null): string
{
    $char_mask ??= " \t\n\r\0\x0B\x0C\u{A0}\u{FEFF}";
    $char_mask   = preg_quote($char_mask, null);

    $string = preg_replace("{^[{$char_mask}]++}uD", '', $string);

    Psl\invariant(null !== $string, 'Expected $string to be a valid UTF-8 string.');

    return $string;
}
