<?php

declare(strict_types=1);

namespace Psl\Math;

use function atan2 as php_atan2;

/**
 * Returns the arc tangent of the given coordinates.
 *
 * @pure
 */
function atan2(float $y, float $x): float
{
    return php_atan2($y, $x);
}
