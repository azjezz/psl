<?php

declare(strict_types=1);

namespace Psl\Dict;

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
 * @param   iterable<Tk, Tv>                $iterable
 * @param   (callable(Tv): Ts)              $scalar_func
 * @param   (callable(Ts, Ts): int)|null    $comparator
 *
 * @return  array<Tk, Tv>
 */
function sort_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
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
    $sorted = namespace\sort($tuples, $tuple_comparator);

    /** @psalm-var array<Tk, Tv> $result */
    $result = [];
    foreach ($sorted as $k => $v) {
        $result[$k] = $v[1];
    }

    return $result;
}
