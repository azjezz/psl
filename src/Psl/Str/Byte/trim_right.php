<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function rtrim;

/**
 * Returns the given string with whitespace stripped from the right.
 *
 * If the optional character mask isn't provided, the following characters will
 * be stripped: space, tab, newline, carriage return, NUL byte, vertical tab.
 *
 * @pure
 */
function trim_right(string $string, null|string $charMask = null): string
{
    return null === $charMask ? rtrim($string) : rtrim($string, $charMask);
}
