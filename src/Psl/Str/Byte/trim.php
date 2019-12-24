<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns the given string with whitespace stripped from the beginning and end.
 *
 * If the optional character mask isn't provided, the following characters will
 * be stripped: space, tab, newline, carriage return, NUL byte, vertical tab.
 */
function trim(string $string, ?string $char_mask = null): string
{
    return null === $char_mask
        ? \trim($string)
        : \trim($string, $char_mask);
}
