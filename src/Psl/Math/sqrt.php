<?php

declare(strict_types=1);

namespace Psl\Math;

use function sqrt as php_sqrt;

/**
 * Returns the square root of the given number.
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $number is negative.
 */
function sqrt(float $number): float
{
    if ($number < 0) {
        throw new Exception\InvalidArgumentException('$number must be a non-negative number.');
    }

    return php_sqrt($number);
}
