<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;

/**
 * Creates an iterable containing all numbers between the start and end value
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
 * @psalm-param T       $start First number (inclusive)
 * @psalm-param T       $end   Last number (inclusive, but doesn't have to be part of
 *                              resulting range if $step steps over it)
 * @psalm-param null|T  $step  Step between numbers (defaults to 1 if $start smaller
 *                              $end and to -1 if $start greater $end)
 *
 * @psalm-return Generator<int, T, mixed, void>
 * @psalm-pure
 *
 * @see https://github.com/vimeo/psalm/issues/2152#issuecomment-533363310
 *
 * @psalm-suppress MixedOperand
 */
function range($start, $end, $step = null): Generator
{
    if ((float) $start === (float) $end) {
        yield $start;
    } elseif ($start < $end) {
        if (null === $step) {
            /** @psalm-var T $step */
            $step = 1;
        } else {
            Psl\invariant($step > 0, 'If start < end, the step must be positive');
        }

        /** @psalm-var T $i */
        for ($i = $start; $i <= $end; $i += $step) {
            yield $i;
        }
    } else {
        if (null === $step) {
            /** @psalm-var T $step */
            $step = -1;
        } else {
            Psl\invariant($step < 0, 'If start > end, the step must be negative');
        }

        /** @psalm-var T $i */
        for ($i = $start; $i >= $end; $i += $step) {
            yield $i;
        }
    }
}
