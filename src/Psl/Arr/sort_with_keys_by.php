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
 * @psalm-param    iterable<Tk, Tv> $iterable
 * @psalm-param    (callable(Tv): Ts) $scalar_func
 * @psalm-param    null|(callable(Ts, Ts): int) $comparator
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
        fn ($a, $b): int => $a <=> $b;

    $tuple_comparator =
        /**
         * @psalm-param array{0: Ts, 1: Tv} $a
         * @psalm-param array{0: Ts, 1: Tv} $b
         */
        fn ($a, $b): int => $comparator($a[0], $b[0]);

    /**
     * @psalm-var Iter\Iterator<Tk, array{0: Ts, 1: Tv}> $tuples
     */
    $tuples = Iter\map(
        $iterable,
        /**
         * @psalm-param  Tv $value
         *
         * @psalm-return array{0: Ts, 1: Tv}
         */
        fn ($value) => [$scalar_func($value), $value],
    );

    $sorted = sort_with_keys($tuples, $tuple_comparator);
    /**
     * @psalm-suppress InvalidArgument
     * @psalm-suppress MixedArgumentTypeCoercion
     */
    $result = Iter\map_with_key(
        $sorted,
        /**
         * @psalm-param  Tk                     $k
         * @psalm-param  array{0: Ts, 1: Tv}    $v
         *
         * @psalm-return Tv
         */
        fn ($k, $v) => $v[1]
    );

    return Iter\to_array_with_keys($result);
}
