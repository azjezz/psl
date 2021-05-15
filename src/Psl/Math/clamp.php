<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;

/**
 * Returns a number whose value is limited to the given range.
 *
 * @template T of float|int
 *
 * @param T $number
 * @param T $min
 * @param T $max
 *
 * @throws Psl\Exception\InvariantViolationException If min is bigger than max
 *
 * @return T
 *
 * @pure
 */
function clamp(int|float $number, int|float $min, int|float $max): int|float
{
    Psl\invariant($min <= $max, 'Expected $min to be lower or equal to $max.');

    if ($number < $min) {
        return $min;
    }

    if ($number > $max) {
        return $max;
    }

    return $number;
}
