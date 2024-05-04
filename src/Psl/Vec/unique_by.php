<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;
use Psl\Iter;

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
    /** @var list<Ts> $unique */
    $unique = [];
    /** @var list<Tv> $original_values */
    $original_values = [];
    foreach ($iterable as $v) {
        $scalar = $scalar_func($v);

        if (!Iter\contains($unique, $scalar)) {
            $unique[] = $scalar;
            $original_values[] = $v;
        }
    }

    return $original_values;
}
