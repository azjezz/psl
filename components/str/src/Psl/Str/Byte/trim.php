<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function trim as php_trim;

/**
 * Returns the given string with whitespace stripped from the beginning and end.
 *
 * If the optional character mask isn't provided, the following characters will
 * be stripped: space, tab, newline, carriage return, NUL byte, vertical tab.
 *
 * @pure
 */
function trim(string $string, null|string $charMask = null): string
{
    return null === $charMask ? php_trim($string) : php_trim($string, $charMask);
}
