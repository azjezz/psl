<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Intermediate values of reducing iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator.
 *
 * The accumulator is initialized to $initial.
 *
 * Reductions yield each accumulator along the way.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tk, Tv>                $iterable
 * @psalm-param (callable(?Ts, Tk, Tv): Ts)     $function
 * @psalm-param Ts|null                         $initial
 *
 * @psalm-return Iterator<int, Ts>
 *
 * @deprecated since 1.2, use Vec\reductions instead.
 *
 * @see Vec\reductions()
 */
function reductions(iterable $iterable, callable $function, $initial = null): Iterator
{
    return Iterator::from(static function () use ($iterable, $function, $initial): Generator {
        $accumulator = $initial;
        foreach ($iterable as $k => $v) {
            $accumulator = $function($accumulator, $k, $v);
            yield $accumulator;
        }
    });
}
