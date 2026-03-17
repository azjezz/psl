<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

/**
 * Returns a cryptographically secure random float in the range from 0.0 to 1.0.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 *
 * @psalm-external-mutation-free
 */
function float(): float
{
    $result = namespace\int(0, 9_007_199_254_740_992) / 9_007_199_254_740_992;

    return (float) $result;
}
