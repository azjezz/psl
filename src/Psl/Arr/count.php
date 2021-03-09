<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns the number of elements an array contains.
 *
 * This function is not recursive, it counts only the number of elements in the
 * array itself, not its children.
 *
 * Examples:
 *
 *      Arr\count([1, 2, 3])
 *      => Int(3)
 *
 *      Arr\count(Arr\flatten([[1, 2, 3], [4], [5, 6], [7, 8]]))
 *      => Int(3)
 *
 *      Arr\count(Arr\flatten([[1, 2, 3], [4], [5, 6], [3 => 7, 4 => 8]]))
 *      => Int(5)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 *
 * @pure
 *
 * @deprecated use Iter\count instead.
 * @see Iter\count()
 */
function count(array $array): int
{
    return \count($array);
}
