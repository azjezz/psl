<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Take the first n elements from an iterable.
 *
 * Examples:
 *
 *      Vec\take([1, 2, 3, 4], 2)
 *      => Vec(1, 2)
 *
 * @template T
 *
 * @param iterable<T> $iterable Iterable to take the elements from
 * @param int<0, max> $n Number of elements to take from the start
 *
 * @return list<T>
 */
function take(iterable $iterable, int $n): array
{
    return slice($iterable, 0, $n);
}
