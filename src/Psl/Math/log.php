<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;

use function log as php_log;

/**
 * Returns the logarithm base of the given number.
 *
 * @pure
 *
 * @throws Psl\Exception\InvariantViolationException If $num or $base are negative, or $base is equal to 1.0.
 */
function log(float $num, ?float $base = null): float
{
    Psl\invariant($num > 0, 'Expected a non-negative number.');
    if (null === $base) {
        return php_log($num);
    }

    Psl\invariant($base > 0, 'Expected a non-negative base.');
    Psl\invariant(1.0 !== $base, 'Logarithm undefined for base 1.');

    return php_log($num, $base);
}
