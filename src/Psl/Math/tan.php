<?php

declare(strict_types=1);

namespace Psl\Math;

use function tan as php_tan;

/**
 * Return the tangent of the given number.
 *
 * @pure
 */
function tan(float $num): float
{
    return php_tan($num);
}
