<?php

declare(strict_types=1);

namespace Psl\Math;

use function round as php_round;

/**
 * Returns the given number rounded to the specified precision.
 *
 * A positive precision rounds to the nearest decimal place whereas a negative precision
 * rounds to the nearest power of ten.
 *
 * For example, a precision of 1 rounds to the nearest tenth whereas a precision of -1 rounds to the nearst nearest.
 *
 * @pure
 */
function round(float $number, int $precision = 0): float
{
    return php_round($number, $precision);
}
