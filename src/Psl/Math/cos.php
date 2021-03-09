<?php

declare(strict_types=1);

namespace Psl\Math;

use function cos as php_cos;

/**
 * Return the cosine of the given number.
 *
 * Example:
 *
 *      Math\cos(0.0)
 *      => Float(1.0)
 *
 *      Math\ceil(1.0)
 *      => Float(0.5403023058681398)
 *
 * @pure
 */
function cos(float $num): float
{
    return php_cos($num);
}
