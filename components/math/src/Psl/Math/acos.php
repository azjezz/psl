<?php

declare(strict_types=1);

namespace Psl\Math;

use function acos as php_acos;

/**
 * Returns the arc cosine of the given number.
 *
 * @pure
 */
function acos(float $number): float
{
    return php_acos($number);
}
