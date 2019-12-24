<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;

/**
 * Calculate the square root of the given number.
 *
 * @param float|int $number
 */
function sqrt(float $number): float
{
    Psl\invariant($number >= 0, 'Expected non-negative number to sqrt, got %f', $number);

    return \sqrt($number);
}
