<?php

declare(strict_types=1);

namespace Psl\Arr;

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
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-pure
 */
function count(array $array): int
{
    return \count($array);
}
