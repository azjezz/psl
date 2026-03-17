<?php

declare(strict_types=1);

namespace Psl\Math;

use function tan as php_tan;

/**
 * Returns the tangent of the given number.
 *
 * @pure
 */
function tan(float $number): float
{
    return php_tan($number);
}
