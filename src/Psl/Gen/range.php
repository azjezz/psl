<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;

/**
 * Creates a generator containing all numbers between the start and end value
 * (inclusive) with a certain step.
 *
 * Examples:
 *
 *     Gen\range(0, 5)
 *     => Gen(0, 1, 2, 3, 4, 5)
 *
 *     Gen\range(5, 0)
 *     => Gen(5, 4, 3, 2, 1, 0)
 *
 *     Gen\range(0.0, 3.0, 0.5)
 *     => Gen(0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0)
 *
 *     Gen\range(3.0, 0.0, -0.5)
 *     => Gen(3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0)
 *
 * @psalm-template T of int|float
 *
 * @psalm-param T       $start First number (inclusive)
 * @psalm-param T       $end   Last number (inclusive, but doesn't have to be part of
 *                              resulting range if $step steps over it)
 * @psalm-param null|T  $step  Step between numbers (defaults to 1 if $start smaller
 *                              $end and to -1 if $start greater $end)
 *
 * @psalm-return Generator<int, T, mixed, void>
 *
 * @psalm-pure
 *
 * @see https://github.com/vimeo/psalm/issues/2152#issuecomment-533363310
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidOperand
 * @psalm-suppress RedundantConditionGivenDocblockType
 * @psalm-suppress DocblockTypeContradiction
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

        Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
        for ($i = $start; $i <= $end; $i += $step) {
            Psl\invariant(is_int($i) || is_float($i), '$i must be either an integer or a float.');
            Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
            yield $i;
        }
    } else {
        if (null === $step) {
            /** @psalm-var T $step */
            $step = -1;
        } else {
            Psl\invariant($step < 0, 'If start > end, the step must be negative');
        }

        Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
        for ($i = $start; $i >= $end; $i += $step) {
            Psl\invariant(is_int($i) || is_float($i), '$i must be either an integer or a float.');
            Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
            yield $i;
        }
    }
}
