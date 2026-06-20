<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a new dict ( keyed-array ) sorted by some scalar property of each value
 * of the given iterable, which is computed by the given function.
 *
 * If the optional comparator function isn't provided, the values will be sorted
 * in ascending order of scalar key.
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): Ts) $scalarFunc
 * @param (Closure(Ts, Ts): int)|null $comparator
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function sort_by<Tk: string|int, Tv, Ts>(iterable $iterable, Closure $scalarFunc, null|Closure $comparator = null): array
{
    $comparator ??=
        /**
         * @mago-expect analysis:possibly-null-operand,possibly-null-operand
         */
        static fn(Ts $a, Ts $b): int => $a <=> $b;

    $tupleComparator =
        /**
         * @param array{0: Ts, 1: Tv} $a
         * @param array{0: Ts, 1: Tv} $b
         */
        static fn(array $a, array $b): int => $comparator($a[0], $b[0]);

    /**
     * @var array<Tk, array{0: Ts, 1: Tv}> $tuples
     */
    $tuples = [];
    foreach ($iterable as $k => $v) {
        $tuples[$k] = [$scalarFunc($v), $v];
    }

    $sorted = namespace\sort::<Tk, array>($tuples, $tupleComparator);

    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($sorted as $k => $v) {
        $result[$k] = $v[1];
    }

    return $result;
}
