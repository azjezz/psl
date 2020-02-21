<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Creates an iterator containing all numbers between the start and end value
 * (inclusive) with a certain step.
 *
 * Examples:
 *
 *     Iter\range(0, 5)
 *     => Iter(0, 1, 2, 3, 4, 5)
 *
 *     Iter\range(5, 0)
 *     => Iter(5, 4, 3, 2, 1, 0)
 *
 *     Iter\range(0.0, 3.0, 0.5)
 *     => Iter(0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0)
 *
 *     Iter\range(3.0, 0.0, -0.5)
 *     => Iter(3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0)
 *
 * @psalm-template T as numeric
 *
 * @psalm-param    T       $start First number (inclusive)
 * @psalm-param    T       $end   Last number (inclusive, but doesn't have to be part of
 *                              resulting range if $step steps over it)
 * @psalm-param    null|T  $step  Step between numbers (defaults to 1 if $start smaller
 *                              $end and to -1 if $start greater $end)
 *
 * @psalm-return   Iterator<int, T>
 *
 * @psalm-pure
 *
 * @see            Gen\range()
 */
function range($start, $end, $step = null): Iterator
{
    return new Iterator(Gen\range($start, $end, $step));
}
