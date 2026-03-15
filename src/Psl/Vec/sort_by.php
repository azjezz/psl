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
 * @param (Closure(Tv): Ts) $scalarFunc
 * @param (Closure(Ts, Ts): int)|null $comparator
 *
 * @return list<Tv>
 */
function sort_by(iterable $iterable, Closure $scalarFunc, null|Closure $comparator = null): array
{
    /** @var array<int, Ts> $orderBy */
    $orderBy = [];
    /** @var array<int, Tv> $values */
    $originalValues = [];

    $i = 0;
    foreach ($iterable as $v) {
        $originalValues[$i] = $v;
        $orderBy[$i] = $scalarFunc($v);

        $i++;
    }

    if (null !== $comparator) {
        uasort($orderBy, $comparator);
    } else {
        asort($orderBy);
    }

    $result = [];
    foreach ($orderBy as $k => $_) {
        $result[] = $originalValues[$k];
    }

    return $result;
}
