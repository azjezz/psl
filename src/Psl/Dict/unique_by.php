<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Iter;

/**
 * Returns a new array in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>   $iterable
 * @psalm-param (callable(Tv): Ts) $scalar_func
 *
 * @psalm-return array<Tk, Tv>
 */
function unique_by(iterable $iterable, callable $scalar_func): array
{
    /** @psalm-var array<Tk, Ts> $unique */
    $unique = [];
    /** @psalm-var array<Tk, Tv> $original_values */
    $original_values = [];
    foreach ($iterable as $k => $v) {
        $original_values[$k] = $v;
        /** @psalm-var Ts $scalar */
        $scalar = $scalar_func($v);

        if (!Iter\contains($unique, $scalar)) {
            $unique[$k] = $scalar;
        }
    }

    /** @psalm-var array<Tk, Tv> $result */
    $result = [];
    foreach ($unique as $k => $_) {
        $result[$k] = $original_values[$k];
    }

    return $result;
}
