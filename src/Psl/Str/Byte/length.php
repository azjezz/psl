<?php

declare(strict_types=1);

namespace Psl\Str\Byte;

/**
 * Returns the length of the given string, i.e. the number of bytes.
 */
function length(string $str): int
{
    return \strlen($str);
}
