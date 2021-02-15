<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Dict\drop([1, 2, 3, 4, 5], 3)
 *      => Dict(4, 5)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable   Iterable to drop the elements from
 * @psalm-param int                 $n          Number of elements to drop from the start
 *
 * @psalm-return array<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 */
function drop(iterable $iterable, int $n): array
{
    return slice($iterable, $n);
}
