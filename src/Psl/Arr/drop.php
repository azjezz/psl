<?php

declare(strict_types=1);

namespace Psl\Arr;

use Generator;
use Psl;

/**
 * Drops the first n items from an array.
 *
 * Examples:
 *
 *      Arr\drop([1, 2, 3, 4, 5], 3)
 *      => Arr(4, 5)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>   $array   Array to drop the elements from
 * @psalm-param int             $n       Number of elements to drop from the start
 *
 * @psalm-return array<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @psalm-pure
 */
function drop(array $array, int $n): array
{
    return slice($array, $n);
}
