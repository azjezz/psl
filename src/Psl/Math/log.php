<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;

use function log as php_log;

/**
 * Returns the logarithm of the given number.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If $number or $base are negative, or $base is equal to 1.0.
 */
function log(float $number, ?float $base = null): float
{
    Psl\invariant($number > 0, 'Expected a positive number.');
    if (null === $base) {
        return php_log($number);
    }

    Psl\invariant($base > 0, 'Expected a positive base.');
    Psl\invariant(1.0 !== $base, 'Logarithm undefined for base 1.');

    return php_log($number, $base);
}
