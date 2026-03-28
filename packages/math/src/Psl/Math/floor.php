<?php

declare(strict_types=1);

namespace Psl\Math;

use function floor as php_floor;

/**
 * Return the largest integer value less then or equal to the given number.
 *
 * @pure
 *
 * @api
 */
function floor(float $number): float
{
    return php_floor($number);
}
