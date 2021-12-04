<?php

declare(strict_types=1);

namespace Psl\SecureRandom;

use Psl\Math;

/**
 * Returns a cryptographically secure random float in the range from 0.0 to 1.0.
 *
 * @throws Exception\InsufficientEntropyException If it was not possible to gather sufficient entropy.
 *
 * @psalm-external-mutation-free
 */
function float(): float
{
    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    $result = namespace\int(0, Math\INT53_MAX) / Math\INT53_MAX;

    return (float) $result;
}
