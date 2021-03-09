<?php

declare(strict_types=1);

namespace Psl\Math;

use function floor as php_floor;

/**
 * Return the largest integer value less then or equal to the given number.
 *
 * @param float|int $num
 *
 * @pure
 */
function floor(float $num): float
{
    return php_floor($num);
}
