<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Merges multiple iterables into a new iterator.
 *
 * Example:
 *      Iter\merge([1, 2], [9, 8])
 *      => Iter(0 => 1, 1 => 2, 0 => 9, 1 => 8)
 *
 *      Iter\merge([0 => 1, 1 => 2], [2 => 9, 3 => 8])
 *      => Iter(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $first
 * @psalm-param    iterable<iterable<Tk, Tv>> $rest
 *
 * @psalm-return Iterator<Tk, Tv>
 *
 * @see Gen\merge()
 */
function merge(iterable $first, iterable ...$rest): Iterator
{
    return new Iterator(Gen\merge($first, ...$rest));
}
