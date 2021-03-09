<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Returns a new list sorted by the values of the given iterable.
 *
 * If the optional comparator function isn't provided, the values will be sorted in
 * ascending order.
 *
 * @template T
 *
 * @param iterable<T> $array
 * @param (callable(T, T): int)|null $comparator
 *
 * @return list<T>
 *
 * @deprecated since 1.2, use Vec\sort instead.
 */
function sort(iterable $array, ?callable $comparator = null): array
{
    return Vec\sort($array, $comparator);
}
