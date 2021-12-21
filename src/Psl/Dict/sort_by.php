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
 * @template Tk of array-key
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): Ts) $scalar_func
 * @param (Closure(Ts, Ts): int)|null $comparator
 *
 * @return array<Tk, Tv>
 */
function sort_by(iterable $iterable, Closure $scalar_func, ?Closure $comparator = null): array
{
    $comparator ??=
        /**
         * @param Ts $a
         * @param Ts $b
         */
        static fn ($a, $b): int => $a <=> $b;

    $tuple_comparator =
        /**
         * @param array{0: Ts, 1: Tv} $a
         * @param array{0: Ts, 1: Tv} $b
         */
        static fn ($a, $b): int => $comparator($a[0], $b[0]);

    /**
     * @var array<Tk, array{0: Ts, 1: Tv}> $tuples
     */
    $tuples = [];
    foreach ($iterable as $k => $v) {
        $tuples[$k] = [$scalar_func($v), $v];
    }

    $sorted = namespace\sort($tuples, $tuple_comparator);

    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($sorted as $k => $v) {
        $result[$k] = $v[1];
    }

    return $result;
}
