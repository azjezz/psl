<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Take the first n elements from an iterable.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to take the elements from
 * @param int<0, max> $n Number of elements to take from the start
 *
 * @return array<Tk, Tv>
 */
function take(iterable $iterable, int $n): array
{
    return slice($iterable, 0, $n);
}
