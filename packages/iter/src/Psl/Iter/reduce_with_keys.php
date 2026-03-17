<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;

/**
 * Reduce iterable with both values and keys using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator key and value and returns a new accumulator.
 *
 * The accumulator is initialized to $initial.
 *
 * @template Tk
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Ts, Tk, Tv): Ts) $function
 * @param Ts $initial
 *
 * @return Ts
 */
function reduce_with_keys(iterable $iterable, Closure $function, mixed $initial): mixed
{
    $accumulator = $initial;
    foreach ($iterable as $k => $v) {
        $accumulator = $function($accumulator, $k, $v);
    }

    return $accumulator;
}
