<?php

declare(strict_types=1);

namespace Psl\Math;

use function cos as php_cos;

/**
 * Return the cosine of the given number.
 *
 * @pure
 */
function cos(float $number): float
{
    return php_cos($number);
}
