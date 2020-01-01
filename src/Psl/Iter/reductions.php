<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Intermediate values of reducing iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * Reductions yield each accumulator along the way.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>                $iterable
 * @psalm-param (callable(?Ts, Tk, Tv): Ts)     $function
 * @psalm-param null|Ts                         $initial
 *
 * @psalm-return Generator<int, Ts, mixed, void>
 */
function reductions(iterable $iterable, callable $function, $initial = null): Generator
{
    $accumulator = $initial;
    foreach ($iterable as $k => $v) {
        $accumulator = $function($accumulator, $k, $v);
        yield $accumulator;
    }
}
