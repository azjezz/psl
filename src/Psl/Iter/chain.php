<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Chains the iterables that were passed as arguments.
 *
 * The resulting iterator will contain the values of the first iterable, then
 * the second, and so on.
 *
 * Examples:
 *
 *     Iter\chain(Iter\range(0, 5), Iter\range(6, 10), Iter\range(11, 15))
 *     => Iter(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15)
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> ...$iterables Iterables to chain
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Vec\concat` instead.
 * @see Vec\concat()
 */
function chain(iterable ...$iterables): Iterator
{
    return Iterator::from(static function () use ($iterables): Generator {
        foreach ($iterables as $iterable) {
            yield from $iterable;
        }
    });
}
