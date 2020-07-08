<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;

/**
 * Calculate the square root of the given number.
 *
 * @param float|int $number
 *
 * @throws Psl\Exception\InvariantViolationException If $number is negative.
 */
function sqrt(float $number): float
{
    Psl\invariant($number >= 0, 'Expected a non-negative number.', $number);

    return \sqrt($number);
}
