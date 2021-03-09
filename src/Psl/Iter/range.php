<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use JetBrains\PhpStorm\Deprecated;
use Psl;
use Psl\Vec;

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
 * @template T of int|float
 *
 * @param T $start First number (inclusive)
 * @param T $end Last number (inclusive, but doesn't have to be part of
 *               resulting range if $step steps over it)
 * @param T|null $step Step between numbers (defaults to 1 if $start smaller
 *                     $end and to -1 if $start greater $end)
 *
 * @throws Psl\Exception\InvariantViolationException If $start < $end, and $step is negative.
 *
 * @return Iterator<int, T>
 *
 * @deprecated since 1.2, use Vec\range instead.
 * @see Vec\range($start, $end, $step)
 */
function range($start, $end, $step = null): Iterator
{
    if ($start < $end) {
        Psl\invariant(null === $step || $step > 0, 'If start < end, the step must be positive.');
    }
    
    if ($start > $end) {
        Psl\invariant(null === $step || $step < 0, 'If start > end, the step must be negative.');
    }

    return Iterator::from(
        /**
         * @return Generator<int, T, mixed, void>
         *
         * @see https://github.com/vimeo/psalm/issues/2152#issuecomment-533363310
         *
         * @psalm-suppress InvalidReturnType
         * @psalm-suppress InvalidOperand
         * @psalm-suppress DocblockTypeContradiction
         */
        static function () use ($start, $end, $step): Generator {
            if ((float) $start === (float) $end) {
                yield $start;
            } elseif ($start < $end) {
                if (null === $step) {
                    /** @var T $step */
                    $step = 1;
                }

                Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
                for ($i = $start; $i <= $end; $i += $step) {
                    Psl\invariant(is_int($i) || is_float($i), '$i must be either an integer or a float.');
                    Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
                    yield $i;
                }
            } else {
                if (null === $step) {
                    /** @var T $step */
                    $step = -1;
                }

                Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
                for ($i = $start; $i >= $end; $i += $step) {
                    Psl\invariant(is_int($i) || is_float($i), '$i must be either an integer or a float.');
                    Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
                    yield $i;
                }
            }
        }
    );
}
