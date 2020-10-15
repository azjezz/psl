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
 * @psalm-template Tk
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>        $iterable
 * @psalm-param (callable(?Ts, Tk): Ts) $function
 * @psalm-param Ts|null                 $initial
 *
 * @psalm-return Ts|null
 */
function reduce_keys(iterable $iterable, callable $function, $initial = null)
{
    $accumulator = $initial;
    foreach ($iterable as $k => $v) {
        $accumulator = $function($accumulator, $k);
    }

    return $accumulator;
}
