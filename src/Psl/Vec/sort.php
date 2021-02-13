<?php

declare(strict_types=1);

namespace Psl\Vec;

use function sort as php_sort;
use function usort;

/**
 * Returns a new list sorted by the values of the given iterable.
 *
 * If the optional comparator function isn't provided, the values will be sorted in
 * ascending order.
 *
 * @template T
 *
 * @param iterable<T>                 $iterable
 * @param (callable(T, T): int)|null  $comparator
 *
 * @return list<T>
 */
function sort(iterable $iterable, ?callable $comparator = null): array
{
    $array = values($iterable);
    if (null !== $comparator) {
        usort($array, $comparator);
    } else {
        php_sort($array);
    }

    return $array;
}
