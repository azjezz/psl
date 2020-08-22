<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Intermediate values of reducing iterable using a function.
 *
 * The reduction function is passed an accumulator value and the current
 * iterator value and returns a new accumulator. The accumulator is initialized
 * to $initial.
 *
 * Reductions returns an iterator with each accumulator along the way.
 *
 * @psalm-template  Tk
 * @psalm-template  Tv
 * @psalm-template  Ts
 *
 * @psalm-param     iterable<Tk, Tv>                $iterable
 * @psalm-param     (callable(?Ts, Tk, Tv): Ts)     $function
 * @psalm-param     null|Ts                         $initial
 *
 * @psalm-return    Iterator<int, Ts>
 *
 * @see             Gen\reductions()
 */
function reductions(iterable $iterable, callable $function, $initial = null): Iterator
{
    return new Iterator(Gen\reductions($iterable, $function, $initial));
}
