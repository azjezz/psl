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
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 * @psalm-param (callable(Tv): Ts) $scalar_func
 * @psalm-param null|(callable(Ts, Ts): int) $comparator
 *
 * @plsam-return array<Tk, Tv>
 */
function sort_with_keys_by(iterable $iterable, callable $scalar_func, ?callable $comparator = null): array
{
    if (null !== $comparator) {
        $tuple_comparator =
            /**
             * @psalm-param array{0: Ts, 1: Tv} $a
             * @psalm-param array{0: Ts, 1: Tv} $b
             */
            static function ($a, $b) use ($comparator): int {
                return $comparator($a[0], $b[0]);
            };
    } else {
        $tuple_comparator =
            /**
             * @psalm-param array{0: Ts, 1: Tv} $a
             * @psalm-param array{0: Ts, 1: Tv} $b
             */
            static function ($a, $b): int {
                return $a[0] <=> $b[0];
            };
    }

    $result = Iter\map(
        sort_with_keys(
            Iter\map(
                $iterable,
                /**
                 * @psalm-param Tv $value
                 *
                 * @psalm-return array{0: Ts, 1: Tv}
                 */
                static function ($value) use ($scalar_func): array {
                    return [$scalar_func($value), $value];
                }
            ),
            $tuple_comparator
        ),
        /**
         * @psalm-param array{0: Ts, 1: Tv} $t
         *
         * @psalm-return Tv
         */
        static function ($t) {
            return $t[1];
        }
    );

    return Iter\to_array($result);
}
