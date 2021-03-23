<?php

declare(strict_types=1);

namespace Psl\PseudoRandom;

use Psl\Math;

/**
 * Returns a random float in the range from 0.0 to 1.0.
 */
function float(): float
{
    /**
     * @psalm-suppress MissingThrowsDocblock $max is always > than $min
     */
    $result = namespace\int(0, Math\INT53_MAX) / Math\INT53_MAX;

    return (float) $result;
}
