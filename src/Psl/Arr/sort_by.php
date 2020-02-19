<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array sorted by some scalar property of each value of the given
 * iterable, which is computed by the given function. If the optional
 * comparator function isn't provided, the values will be sorted in ascending
 * order of scalar key.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 * @psalm-param (callable(Tv): Ts) $scalar_func
 * @psalm-param null|(callable(Ts, Ts): int) $comparator
 *
 * @plsam-return array<int, Tv>
 */
function sort_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    /** @psalm-var array<Tk, Tv> $values */
    $values = Iter\to_array_with_keys($iterable);
    /** @psalm-var array<Tk, Ts> $order_by */
    $order_by = Iter\to_array_with_keys(Iter\map($values, $scalar_func));

    if (null !== $comparator) {
        \uasort($order_by, $comparator);
    } else {
        \asort($order_by);
    }

    return Iter\to_array(Iter\map_with_key(
        $order_by,
        /**
         * @psalm-param Tk $k
         * @psalm-param Ts $_
         */
        fn ($k, $_) => $values[$k]
    ));
}
