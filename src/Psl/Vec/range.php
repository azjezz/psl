<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl;

/**
 * Returns a new list containing the range of numbers from `$start` to `$end`
 * inclusive, with the step between elements being `$step` if provided, or 1 by
 * default.
 *
 * If `$start > $end`, it returns a descending range instead of
 * an empty one.
 *
 * If you don't need the items to be enumerated, consider Vec\fill.
 *
 * Examples:
 *
 *     Vec\range(0, 5)
 *     => Vec(0, 1, 2, 3, 4, 5)
 *
 *     Vec\range(5, 0)
 *     => Vec(5, 4, 3, 2, 1, 0)
 *
 *     Vec\range(0.0, 3.0, 0.5)
 *     => Vec(0.0, 0.5, 1.0, 1.5, 2.0, 2.5, 3.0)
 *
 *     Vec\range(3.0, 0.0, -0.5)
 *     => Vec(3.0, 2.5, 2.0, 1.5, 1.0, 0.5, 0.0)
 *
 * @template T of int|float
 *
 * @param T $start
 * @param T $end
 * @param T|null $step
 *
 * @return list<T>
 *
 * @throws Psl\Exception\InvariantViolationException If $start < $end, and $step is negative.
 *
 * @psalm-suppress InvalidReturnType
 * @psalm-suppress InvalidReturnStatement
 * @psalm-suppress InvalidOperand
 * @psalm-suppress RedundantConditionGivenDocblockType
 * @psalm-suppress DocblockTypeContradiction
 *
 * @see https://github.com/vimeo/psalm/issues/2152#issuecomment-533363310
 */
function range($start, $end, $step = null): array
{
    if ((float) $start === (float) $end) {
        return [$start];
    }

    if ($start < $end) {
        if (null === $step) {
            /** @psalm-var T $step */
            $step = 1;
        }

        Psl\invariant($step > 0, 'If $start < $end, then $step must be positive or null.');

        $result = [];
        Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
        for ($i = $start; $i <= $end; $i += $step) {
            Psl\invariant(is_int($i) || is_float($i), '$i must be either an integer or a float.');
            Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
            $result[] = $i;
        }

        return $result;
    }

    if (null === $step) {
        /** @psalm-var T $step */
        $step = -1;
    }

    Psl\invariant($step < 0, 'If $start > $end, then $step must be negative or null.');

    $result = [];
    Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
    for ($i = $start; $i >= $end; $i += $step) {
        Psl\invariant(is_int($i) || is_float($i), '$i must be either an integer or a float.');
        Psl\invariant(is_int($step) || is_float($step), '$step must be either an integer or a float.');
        $result[] = $i;
    }

    return $result;
}
