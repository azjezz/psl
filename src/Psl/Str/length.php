<?php

declare(strict_types=1);

namespace Psl\Str;

/**
 * Returns the length of the given string, i.e. the number of bytes.
 *
 * Example:
 *
 *      Str\length('Hello')
 *      => Int(5)
 *
 *      Str\length('سيف')
 *      => Int(3)
 *
 *      Str\length('تونس')
 *      => Int(4)
 */
function length(string $str): int
{
    return \mb_strlen($str, encoding($str));
}
