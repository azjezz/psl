<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strlen;

/**
 * Returns the length of the given string, i.e. the number of bytes.
 *
 * @pure
 */
function length(string $string): int
{
    return strlen($string);
}
