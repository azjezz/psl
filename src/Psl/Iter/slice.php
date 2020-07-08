<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;
use Psl\Gen;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => iter(0, 1, 2, 3, 4, 5)
 *
 *      Iter\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Iter(0, 1, 2, 3)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk,Tv>     $iterable Iterable to take the slice from
 * @psalm-param    int                 $start Start offset
 * @psalm-param    int                 $length Length (if not specified all remaining values from the
 *                                      iterable are used)
 *
 * @psalm-return   Iterator<Tk, Tv>
 *
 * @see            Gen\slice()
 *
 * @throws Psl\Exception\InvariantViolationException If the $start offset or $length are negative
 */
function slice(iterable $iterable, int $start, ?int $length = null): Iterator
{
    return new Iterator(Gen\slice($iterable, $start, $length));
}
