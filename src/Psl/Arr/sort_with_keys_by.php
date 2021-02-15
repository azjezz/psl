<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

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
 * @psalm-param    iterable<Tk, Tv>                $iterable
 * @psalm-param    (callable(Tv): Ts)              $scalar_func
 * @psalm-param    (callable(Ts, Ts): int)|null    $comparator
 *
 * @psalm-return   array<Tk, Tv>
 *
 * @deprecated use `Dict\sort_by` instead
 *
 * @see Dict\sort_by()
 */
function sort_with_keys_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    return Dict\sort_by($iterable, $scalar_func, $comparator);
}
