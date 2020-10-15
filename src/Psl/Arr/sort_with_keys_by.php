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
 * @psalm-param    iterable<Tk, Tv>                $iterable
 * @psalm-param    (callable(Tv): Ts)              $scalar_func
 * @psalm-param    (callable(Ts, Ts): int)|null    $comparator
 *
 * @psalm-return   array<Tk, Tv>
 */
function sort_with_keys_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    $comparator ??=
        /**
         * @psalm-param Ts $a
         * @psalm-param Ts $b
         */
        static fn ($a, $b): int => $a <=> $b;

    $tuple_comparator =
        /**
         * @psalm-param array{0: Ts, 1: Tv} $a
         * @psalm-param array{0: Ts, 1: Tv} $b
         */
        static fn ($a, $b): int => $comparator($a[0], $b[0]);

    /**
     * @psalm-var array<Tk, array{0: Ts, 1: Tv}> $tuples
     */
    $tuples = [];
    foreach ($iterable as $k => $v) {
        $tuples[$k] = [$scalar_func($v), $v];
    }

    /**
     * @psalm-var array<Tk, array{0: Ts, 1: Tv}> $sorted
     */
    $sorted = sort_with_keys($tuples, $tuple_comparator);

    /** @psalm-var array<Tk, Tv> $result */
    $result = [];
    foreach ($sorted as $k => $v) {
        $result[$k] = $v[1];
    }

    return $result;
}
