<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Reduce iterable keys using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Ts, Tk): Ts) $function
 * @param Ts $initial
 *
 * @return Ts
 *
 * @api
 */
function reduce_keys<Tk = mixed, Tv = mixed, Ts = mixed>(iterable $iterable, Closure $function, Ts $initial): Ts
{
    $accumulator = $initial;
    foreach ($iterable as $k => $_) {
        $accumulator = $function($accumulator, $k);
    }

    return $accumulator;
}
