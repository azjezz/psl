<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Reduce iterable with both values and keys using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * Examples:
 *
 *      Iter\reduce_with_keys(
 *          Iter\range(1, 5),
 *          static fn(int $accumulator, int $key, int $value): int => $accumulator + $value,
 *          0,
  *     )
 *      => 15
 *
 *      Iter\reduce_with_keys(
 *          Iter\range(1, 5),
 *          static fn(int $accumulator, int $key, int $value): int => $accumulator * $value,
 *          0,
 *     )
 *      => 120
 *
 * @psalm-template Tk
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>                $iterable
 * @psalm-param (callable(?Ts, Tk, Tv): Ts)     $function
 * @psalm-param Ts|null                         $initial
 *
 * @psalm-return Ts|null
 */
function reduce_with_keys(iterable $iterable, callable $function, $initial = null)
{
    $accumulator = $initial;
    foreach ($iterable as $k => $v) {
        $accumulator = $function($accumulator, $k, $v);
    }

    return $accumulator;
}
