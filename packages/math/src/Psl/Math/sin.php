<?php

declare(strict_types=1);

namespace Psl\Math;

use function sin as php_sin;

/**
 * Returns the sine of the given number.
 *
 * @pure
 *
 * @api
 */
function sin(float $number): float
{
    return php_sin($number);
}
