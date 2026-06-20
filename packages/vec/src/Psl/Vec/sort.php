<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function sort as php_sort;
use function usort;

/**
 * Returns a new list sorted by the values of the given iterable.
 *
 * If the optional comparator function isn't provided, the values will be sorted in
 * ascending order.
 *
 * @param iterable<T> $iterable
 * @param (Closure(T, T): int)|null $comparator
 *
 * @return list<T>
 *
 * @api
 */
function sort<T>(iterable $iterable, null|Closure $comparator = null): array
{
    $array = namespace\values::<mixed>($iterable);
    if (null !== $comparator) {
        usort($array, $comparator);

        return $array;
    }

    php_sort($array);

    return $array;
}
