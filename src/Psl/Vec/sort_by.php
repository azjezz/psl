<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

use function asort;
use function uasort;

/**
 * Returns a new list sorted by some scalar property of each value of the given
 * iterable, which is computed by the given function.
 *
 * If the optional comparator function isn't provided, the values will be sorted
 * in ascending order of scalar key.
 *
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tv> $iterable
 * @param (Closure(Tv): Ts) $scalar_func
 * @param (Closure(Ts, Ts): int)|null $comparator
 *
 * @return list<Tv>
 */
function sort_by(iterable $iterable, Closure $scalar_func, ?Closure $comparator = null): array
{
    /** @var array<int, Ts> $order_by */
    $order_by = [];
    /** @var array<int, Tv> $values */
    $original_values = [];

    $i = 0;
    foreach ($iterable as $v) {
        $original_values[$i] = $v;
        $order_by[$i] = $scalar_func($v);

        $i++;
    }

    if (null !== $comparator) {
        uasort($order_by, $comparator);
    } else {
        asort($order_by);
    }

    $result = [];
    foreach ($order_by as $k => $_) {
        $result[] = $original_values[$k];
    }

    return $result;
}
