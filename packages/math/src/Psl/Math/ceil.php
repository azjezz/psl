<?php

declare(strict_types=1);

namespace Psl\Math;

use function ceil as php_ceil;

/**
 * Return the smallest integer value greater than or equal to the given number.
 *
 * @pure
 *
 * @api
 */
function ceil(float $number): float
{
    return php_ceil($number);
}
