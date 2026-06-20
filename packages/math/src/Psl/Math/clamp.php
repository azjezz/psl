<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the given number clamped to the given range.
 *
 * @throws Exception\InvalidArgumentException If $min is bigger than $max
 *
 * @pure
 *
 * @api
 */
function clamp<T: float|int>(T $number, T $min, T $max): T
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
