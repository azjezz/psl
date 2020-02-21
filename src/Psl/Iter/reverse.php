<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Reverse the given iterable.
 *
 * Example:
 *      Iter\reverse(['foo', 'bar', 'baz', 'qux'])
 *      => Iter('qux', 'baz', 'bar', 'foo')
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<T> $iterable The iterable to reverse.
 *
 * @psalm-return   Iterator<int, T>
 *
 * @see            Gen\reverse()
 */
function reverse(iterable $iterable): Iterator
{
    return new Iterator(Gen\reverse($iterable));
}
