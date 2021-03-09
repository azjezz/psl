<?php

declare(strict_types=1);

namespace Psl\Str;

use function implode;

/**
 * Join array elements with a string.
 *
 * Example:
 *
 *      Str\join(['a', 'b', 'c'], ' / ')
 *      => Str('a / b / c')
 *
 *      Str\join(['Hello', 'World'], ', ')
 *      => Str('Hello, World')
 *
 * @param list<string> $pieces the array of strings to implode
 *
 * @return string a string containing a string representation of all the array
 *                elements in the same order, with the glue string between each element
 *
 * @pure
 */
function join(array $pieces, string $glue): string
{
    return implode($glue, $pieces);
}
