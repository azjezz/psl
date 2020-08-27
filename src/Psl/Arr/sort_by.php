<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array sorted by some scalar property of each value of the given
 * array, which is computed by the given function. If the optional comparator
 * function isn't provided, the values will be sorted in ascending order
 * of scalar key.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param array<Tk, Tv>                       $array
 * @psalm-param (pure-callable(Tv): Ts)             $scalar_func
 * @psalm-param null|(pure-callable(Ts, Ts): int)   $comparator
 *
 * @psalm-return list<Tv>
 *
 * @psalm-pure
 */
function sort_by(array $array, callable $scalar_func, ?callable $comparator = null): array
{
    /** @psalm-var array<Tk, Ts> $order_by */
    $order_by = [];
    foreach ($array as $k => $v) {
        $order_by[$k] = $scalar_func($v);
    }

    if (null !== $comparator) {
        \uasort($order_by, $comparator);
    } else {
        \asort($order_by);
    }

    $result = [];
    foreach ($order_by as $k => $_) {
        $result[] = $array[$k];
    }

    return $result;
}
