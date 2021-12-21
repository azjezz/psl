<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;
use Psl\Iter;

/**
 * Returns a new array in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function.
 *
 * @template Tk of array-key
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tv): Ts) $scalar_func
 *
 * @return array<Tk, Tv>
 */
function unique_by(iterable $iterable, Closure $scalar_func): array
{
    /** @var array<Tk, Ts> $unique */
    $unique = [];
    /** @var array<Tk, Tv> $original_values */
    $original_values = [];
    foreach ($iterable as $k => $v) {
        $original_values[$k] = $v;
        $scalar = $scalar_func($v);

        if (!Iter\contains($unique, $scalar)) {
            $unique[$k] = $scalar;
        }
    }

    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($unique as $k => $_) {
        $result[$k] = $original_values[$k];
    }

    return $result;
}
