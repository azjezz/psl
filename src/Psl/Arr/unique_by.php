<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;
use Psl\Str;

/**
 * Returns a new array in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts of array-key
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 * @psalm-param (callable(Tv): Ts)  $scalar_func
 *
 * @psalm-return array<Tk, Tv>
 * @return array
 */
function unique_by(iterable $iterable, callable $scalar_func): array
{
    /** @psalm-var array<Tk, Tv> $iterable */
    $iterable = Iter\to_array_with_keys($iterable);
    /** @psalm-var array<Tk, Ts> $unique */
    $unique = [];
    foreach ($iterable as $k => $v) {
        /** @psalm-var Ts $scalar */
        $scalar = $scalar_func($v);

        if (!contains($unique, $scalar)) {
            $unique[$k] = $scalar;
        }
    }

    /** @psalm-var \Generator<Tk, Tv, mixed, void> $unique */
    $unique = Iter\map_with_key(
        $unique,
        /**
         * @psalm-param Tk $k
         * @psalm-param Ts $v
         *
         * @psalm-return Tv
         */
        fn ($k, $v) => $iterable[$k]
    );

    /** @psalm-var array<Tk, Tv> */
    return Iter\to_array_with_keys($unique);
}
