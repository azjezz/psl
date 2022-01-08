<?php

declare(strict_types=1);

namespace Psl\Math;

use function log as php_log;

/**
 * Returns the logarithm of the given number.
 *
 * @pure
 *
 * @throws Exception\InvalidArgumentException If $number or $base are negative, or $base is equal to 1.0.
 */
function log(float $number, ?float $base = null): float
{
    if ($number <= 0) {
        throw new Exception\InvalidArgumentException('$number must be positive.');
    }

    if (null === $base) {
        return php_log($number);
    }

    if ($base <= 0) {
        throw new Exception\InvalidArgumentException('$base must be positive.');
    }

    if ($base === 1.0) {
        throw new Exception\InvalidArgumentException('Logarithm undefined for $base of 1.0.');
    }

    return php_log($number, $base);
}
