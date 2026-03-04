<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * Returns a new array in which each value appears exactly once, where the
 * value's uniqueness is determined by transforming it to a scalar via the
 * given function.
 *
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tv> $iterable
 * @param (Closure(Tv): Ts) $scalar_func
 *
 * @return list<Tv>
 */
function unique_by(iterable $iterable, Closure $scalar_func): array
{
    /** @var array<array-key, true> $seen */
    $seen = [];
    /** @var list<Tv> $result */
    $result = [];
    foreach ($iterable as $v) {
        $scalar = $scalar_func($v);
        $key = is_int($scalar) || is_string($scalar) ? $scalar : serialize($scalar);

        if (!isset($seen[$key])) {
            $seen[$key] = true;
            $result[] = $v;
        }
    }

    return $result;
}
