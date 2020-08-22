<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns the values of an iterable, making the keys continuously indexed.
 *
 * Examples:
 *
 *      Iter\values(['a' => 'foo', 'b' => 'bar', 'c' => 'baz'])
 *      => Iter('foo', 'bar', 'baz')
 *
 *      Iter\values([17 => 1, 42 => 2, -2 => 100])
 *      => Iter(0 => 1, 1 => 42, 2 => 100)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable Iterable to get values from
 *
 * @psalm-return   Iterator<int, Tv>
 *
 * @see            Gen\values()
 */
function values(iterable $iterable): Iterator
{
    return new Iterator(Gen\values($iterable));
}
