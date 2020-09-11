<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array sorted by some scalar property of each value of the given
 * iterable, which is computed by the given function.
 *
 * If the optional comparator function isn't provided, the values will be sorted
 * in ascending order of scalar key.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>               $iterable
 * @psalm-param (callable(Tv): Ts)             $scalar_func
 * @psalm-param (callable(Ts, Ts): int)|null   $comparator
 *
 * @psalm-return list<Tv>
 */
function sort_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    /** @psalm-var array<Tk, Ts> $order_by */
    $order_by = [];
    /** @psalm-var array<Tk, Tv> $values */
    $original_values = [];
    foreach ($iterable as $k => $v) {
        $original_values[$k] = $v;
        $order_by[$k] = $scalar_func($v);
    }

    if (null !== $comparator) {
        \uasort($order_by, $comparator);
    } else {
        \asort($order_by);
    }

    $result = [];
    foreach ($order_by as $k => $_) {
        $result[] = $original_values[$k];
    }

    return $result;
}
