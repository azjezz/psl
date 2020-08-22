<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Zips the iterables that were passed as arguments.
 *
 *  Afterwards keys and values will be arrays containing the keys/values of
 *  the individual iterables. This function stops as soon as the first iterable
 *  becomes invalid.
 *
 *  Examples:
 *
 *     Iter\zip([1, 2, 3], [4, 5, 6], [7, 8, 9, 10])
 *     => Iter(
 *         Arr(0, 0, 0) => Arr(1, 4, 7),
 *         Arr(1, 1, 1) => Arr(2, 5, 8),
 *         Arr(2, 2, 2) => Arr(3, 6, 9)
 *     )
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> ...$iterables
 *
 * @psalm-return   Iterator<list<Tk>, list<Tv>>
 *
 * @see            Gen\zip()
 */
function zip(iterable ...$iterables): Iterator
{
    return new Iterator(Gen\zip(...$iterables));
}
