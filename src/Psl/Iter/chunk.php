<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Chunks an iterable into arrays of the specified size.
 *
 * Examples:
 *
 *      Iter\chunk([1, 2, 3, 4, 5], 2)
 *      => Iter([1, 2], [3, 4], [5])
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<T> $iterable The iterable to chunk
 * @psalm-param    int         $size     The size of each chunk
 *
 * @psalm-return   Iterator<int, T>
 *
 * @see            Gen\chunk()
 */
function chunk(iterable $iterable, int $size): Iterator
{
    return new Iterator(Gen\chunk($iterable, $size));
}
