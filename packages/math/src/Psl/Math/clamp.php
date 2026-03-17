<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the given number clamped to the given range.
 *
 * @template T of float|int
 *
 * @param T $number
 * @param T $min
 * @param T $max
 *
 * @throws Exception\InvalidArgumentException If $min is bigger than $max
 *
 * @return T
 *
 * @pure
 */
function clamp(int|float $number, int|float $min, int|float $max): int|float
{
    if ($max < $min) {
        throw new Exception\InvalidArgumentException('Expected $min to be lower or equal to $max.');
    }

    if ($number < $min) {
        return $min;
    }

    if ($number > $max) {
        return $max;
    }

    return $number;
}
