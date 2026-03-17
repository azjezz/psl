<?php

declare(strict_types=1);

namespace Psl\PseudoRandom;

/**
 * Returns a pseudo-random float in the range of [0.0, 1.0].
 *
 * @psalm-external-mutation-free
 */
function float(): float
{
    $result = namespace\int(0, 9_007_199_254_740_992) / 9_007_199_254_740_992;

    return (float) $result;
}
