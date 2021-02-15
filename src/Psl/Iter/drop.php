<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Dict;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Iter\drop([1, 2, 3, 4, 5], 3)
 *      => Iter(4, 5)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable   Iterable to drop the elements from
 * @psalm-param int                 $n          Number of elements to drop from the start
 *
 * @psalm-return Iterator<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @deprecated use `Dict\drop` instead.
 *
 * @see Dict\drop()
 */
function drop(iterable $iterable, int $n): Iterator
{
    /** @psalm-suppress DeprecatedFunction */
    return slice($iterable, $n);
}
