<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Reduce iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * Examples:
 *
 *      Iter\reduce(Iter\range(1, 5), fn($accumulator, $value) => $accumulator + $value, 0)
 *      => 15
 *
 *      Iter\reduce(Iter\range(1, 5), fn($accumulator, $value) => $accumulator * $value, 1)
 *      => 120
 *
 * @template Tk
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Ts, Tv): Ts) $function
 * @param Ts $initial
 *
 * @return Ts
 */
function reduce(iterable $iterable, callable $function, $initial)
{
    $accumulator = $initial;
    foreach ($iterable as $v) {
        $accumulator = $function($accumulator, $v);
    }

    return $accumulator;
}
