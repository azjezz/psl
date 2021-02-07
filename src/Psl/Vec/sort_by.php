<?php

declare(strict_types=1);

namespace Psl\Vec;

use function asort;
use function uasort;

/**
 * Returns a new list sorted by some scalar property of each value of the given
 * iterable, which is computed by the given function.
 *
 * If the optional comparator function isn't provided, the values will be sorted
 * in ascending order of scalar key.
 *
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tv>                   $iterable
 * @psalm-param (callable(Tv): Ts)             $scalar_func
 * @psalm-param (callable(Ts, Ts): int)|null   $comparator
 *
 * @psalm-return list<Tv>
 */
function sort_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    /** @psalm-var array<int, Ts> $order_by */
    $order_by = [];
    /** @psalm-var array<int, Tv> $values */
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
