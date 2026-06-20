<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Reduces an iterable to a single value.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * @param iterable<T> $iterable
 * @param (Closure(Ts, T): Ts) $function
 *
 * @api
 */
function reduce<T, Ts>(iterable $iterable, Closure $function, Ts $initial): Ts
{
    $accumulator = $initial;
    foreach ($iterable as $v) {
        $accumulator = $function($accumulator, $v);
    }

    return $accumulator;
}
