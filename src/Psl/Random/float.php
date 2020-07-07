<?php

declare(strict_types=1);

namespace Psl\Random;

use Psl\Math;

/**
 * Returns a cryptographically secure random float in the range from 0.0 to 1.0.
 */
function float(): float
{
    /** @var float|int $result */
    $result = int(0, Math\INT53_MAX) / Math\INT53_MAX;

    return (float) $result;
}
