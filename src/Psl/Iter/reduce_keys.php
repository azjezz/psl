<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Reduce iterable keys using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * Examples:
 *
 *      Iter\reduce_keys(
 *          Iter\range(1, 5),
 *          static fn(int $accumulator, int $key): int => $accumulator + $key,
 *          0,
 *     )
 *      => 10
 *
 * @template Tk
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(?Ts, Tk): Ts) $function
 * @param Ts|null $initial
 *
 * @return Ts|null
 */
function reduce_keys(iterable $iterable, callable $function, $initial = null)
{
    $accumulator = $initial;
    foreach ($iterable as $k => $_v) {
        $accumulator = $function($accumulator, $k);
    }

    return $accumulator;
}
