<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * Intermediate values of reducing iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator.
 *
 * The accumulator is initialized to $initial.
 *
 * Reductions returns a list of every accumulator throughout the way.
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Ts, Tk, Tv): Ts) $function
 * @param Ts $initial
 *
 * @return list<Ts>
 *
 * @api
 */
function reductions<Tk, Tv, Ts>(iterable $iterable, Closure $function, Ts $initial): array
{
    $accumulators = [];
    $accumulator = $initial;
    foreach ($iterable as $k => $v) {
        $accumulator = $function($accumulator, $k, $v);
        $accumulators[] = $accumulator;
    }

    return $accumulators;
}
