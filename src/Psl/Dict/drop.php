<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Dict\drop([1, 2, 3, 4, 5], 3)
 *      => Dict(4, 5)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to drop the elements from
 * @param int<0, max> $n Number of elements to drop from the start
 *
 * @return array<Tk, Tv>
 */
function drop(iterable $iterable, int $n): array
{
    return slice($iterable, $n);
}
