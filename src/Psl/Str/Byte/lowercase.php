<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strtolower;

/**
 * Returns the string with all alphabetic characters converted to lowercase.
 *
 * @pure
 */
function lowercase(string $string): string
{
    return strtolower($string);
}
