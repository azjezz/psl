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
 * @psalm-param array<Tk, Tv>           $array
 * @psalm-param (pure-callable(Tv): Ts) $scalar_func
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function unique_by(array $array, callable $scalar_func): array
{
    /** @psalm-var array<Tk, Ts> $unique */
    $unique = [];
    foreach ($array as $k => $v) {
        /** @psalm-var Ts $scalar */
        $scalar = $scalar_func($v);

        if (!contains($unique, $scalar)) {
            $unique[$k] = $scalar;
        }
    }

    /** @psalm-var array<Tk, Tv> $result */
    $result = [];
    foreach ($unique as $k => $_) {
        $result[$k] = $array[$k];
    }

    return $result;
}
