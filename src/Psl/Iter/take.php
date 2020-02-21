<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Take the first n elements from an iterable.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable
 *
 * @psalm-return   Iterator<Tk, Tv>
 */
function take(iterable $iterable, int $n): Iterator
{
    return slice($iterable, 0, $n);
}
