<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array sorted by some scalar property of each value of the given
 * array, which is computed by the given function. If the optional
 * comparator function isn't provided, the values will be sorted in ascending
 * order of scalar key.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param    array<Tk, Tv> $array
 * @psalm-param    (pure-callable(Tv): Ts) $scalar_func
 * @psalm-param    null|(pure-callable(Ts, Ts): int) $comparator
 *
 * @psalm-return   array<Tk, Tv>
 *
 * @psalm-pure
 */
function sort_with_keys_by(array $array, callable $scalar_func, ?callable $comparator = null): array
{
    $comparator ??=
        /**
         * @psalm-param Ts $a
         * @psalm-param Ts $b
         *
         * @psalm-pure
         */
        fn ($a, $b): int => $a <=> $b;

    $tuple_comparator =
        /**
         * @psalm-param array{0: Ts, 1: Tv} $a
         * @psalm-param array{0: Ts, 1: Tv} $b
         *
         * @psalm-pure
         */
        fn ($a, $b): int => $comparator($a[0], $b[0]);

    /**
     * @psalm-var array<Tk, array{0: Ts, 1: Tv}> $tuples
     */
    $tuples = [];
    foreach ($array as $k => $v) {
        $tuples[$k] = [$scalar_func($v), $v];
    }

    /**
     * @psalm-var array<Tk, array{0: Ts, 1: Tv}> $sorted
     */
    $sorted = sort_with_keys($tuples, $tuple_comparator);

    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($sorted as $k => $v) {
        $result[$k] = $v[1];
    }

    return $result;
}
