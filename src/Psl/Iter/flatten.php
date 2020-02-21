<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns an iterator formed by merging the iterable elements of the
 * given iterable.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<iterable<Tk, Tv>> $iterables
 *
 * @psalm-return   Iterator<Tk, Tv>
 *
 * @see            Gen\flatten()
 */
function flatten(iterable $iterables): Iterator
{
    return new Iterator(Gen\flatten($iterables));
}
