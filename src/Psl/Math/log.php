<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl;

/**
 * Returns the logarithm base of the given number.
 */
function log(float $num, ?float $base = null): float
{
    Psl\invariant($num > 0, 'Expected positive number for log, got %f', $num);
    if (null === $base) {
        return \log($num);
    }

    Psl\invariant($base > 0, 'Expected positive base for log, got %f', $base);
    Psl\invariant(1.0 !== $base, 'Logarithm undefined for base 1');

    return \log($num, $base);
}
