<?php

declare(strict_types=1);

namespace Psl\Vec;

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
 * @psalm-template Tk
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>            $iterable
 * @psalm-param (callable(Ts, Tk, Tv): Ts)  $function
 * @psalm-param Ts                          $initial
 *
 * @psalm-return list<Ts>
 */
function reductions(iterable $iterable, callable $function, $initial): array
{
    $accumulators = [];
    $accumulator = $initial;
    foreach ($iterable as $k => $v) {
        $accumulator = $function($accumulator, $k, $v);
        $accumulators[] = $accumulator;
    }

    return $accumulators;
}
