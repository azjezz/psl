<?php

declare(strict_types=1);

namespace Psl\Math;

use function asin as php_asin;

/**
 * Returns the arc sine of the given number.
 *
 * @pure
 */
function asin(float $number): float
{
    return php_asin($number);
}
