<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

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
 *
 * @deprecated since 1.2, use Vec\sort_by instead.
 */
function sort_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    return Vec\sort_by($iterable, $scalar_func, $comparator);
}
