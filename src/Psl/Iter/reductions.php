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
 * @template Tk
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(?Ts, Tk, Tv): Ts) $function
 * @param Ts|null $initial
 *
 * @return Iterator<int, Ts>
 *
 * @deprecated since 1.2, use Vec\reductions instead.
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
