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
 * @template Tk
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Ts, Tk): Ts) $function
 * @param Ts $initial
 *
 * @return Ts
 */
function reduce_keys(iterable $iterable, Closure $function, mixed $initial): mixed
{
    $accumulator = $initial;
    foreach ($iterable as $k => $_v) {
        $accumulator = $function($accumulator, $k);
    }

    return $accumulator;
}
