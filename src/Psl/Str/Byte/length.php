<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

use function strlen;

/**
 * Returns the length of the given string, i.e. the number of bytes.
 *
 * @return int<0, max>
 *
 * @pure
 */
function length(string $string): int
{
    return strlen($string);
}
