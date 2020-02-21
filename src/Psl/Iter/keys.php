<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns the keys of an iterable.
 *
 * Examples:
 *
 *      Iter\keys(['a' => 0, 'b' => 1, 'c' => 2])
 *      => Iter('a', 'b', 'c')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable Iterable to get keys from
 *
 * @psalm-return   Iterator<int, Tk>
 *
 * @see            Gen\keys()
 */
function keys(iterable $iterable): Iterator
{
    return new Iterator(Gen\keys($iterable));
}
