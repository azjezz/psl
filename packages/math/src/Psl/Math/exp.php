<?php

declare(strict_types=1);

namespace Psl\Math;

use function exp as php_exp;

/**
 * Returns the exponential of the given number.
 *
 * @pure
 */
function exp(float $number): float
{
    return php_exp($number);
}
