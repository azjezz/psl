<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Drops the first n items from an iterable.
 *
 * Examples:
 *
 *      Vec\drop([1, 2, 3, 4, 5], 3)
 *      => Vec(4, 5)
 *
 * @template T
 *
 * @param iterable<T> $iterable Iterable to drop the elements from
 * @param int<0, max> $n Number of elements to drop from the start
 *
 * @return list<T>
 */
function drop(iterable $iterable, int $n): array
{
    return slice($iterable, $n);
}
