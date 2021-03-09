<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Dict;

/**
 * Drops the first n items from an array.
 *
 * Examples:
 *
 *      Arr\drop([1, 2, 3, 4, 5], 3)
 *      => Arr(4, 5)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array Array to drop the elements from
 * @param int $n Number of elements to drop from the start
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\drop` instead.
 * @see Dict\drop()
 */
function drop(iterable $array, int $n): array
{
    return Dict\drop($array, $n);
}
