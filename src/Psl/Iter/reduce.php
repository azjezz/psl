<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Reduces an iterable to a single value.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * @template T
 * @template Ts
 *
 * @param iterable<T> $iterable
 * @param (callable(Ts, T): Ts) $function
 * @param Ts $initial
 *
 * @return Ts
 */
function reduce(iterable $iterable, callable $function, mixed $initial): mixed
{
    $accumulator = $initial;
    foreach ($iterable as $v) {
        $accumulator = $function($accumulator, $v);
    }

    return $accumulator;
}
