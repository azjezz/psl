<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

use function is_int;
use function is_string;
use function serialize;

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
 * @param (Closure(Tv): Ts) $scalarFunc
 *
 * @return array<Tk, Tv>
 */
function unique_by(iterable $iterable, Closure $scalarFunc): array
{
    /** @var array<array-key, true> $seen */
    $seen = [];
    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($iterable as $k => $v) {
        $scalar = $scalarFunc($v);
        $key = is_int($scalar) || is_string($scalar) ? $scalar : serialize($scalar);

        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $result[$k] = $v;
        }
    }

    return $result;
}
