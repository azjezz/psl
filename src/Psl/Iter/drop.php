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
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to drop the elements from
 * @param int $n Number of elements to drop from the start
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\drop` instead.
 * @see Dict\drop()
 */
function drop(iterable $iterable, int $n): Iterator
{
    /** @psalm-suppress DeprecatedFunction */
    return slice($iterable, $n);
}
