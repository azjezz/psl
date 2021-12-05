<?php

declare(strict_types=1);

namespace Psl\Math;

use function atan as php_atan;

/**
 * Returns the arc tangent of the given number.
 *
 * @pure
 */
function atan(float $number): float
{
    return php_atan($number);
}
