<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
 *
 * @psalm-mutation-free
 */
function between(int $lower_bound, int $upper_bound, bool $upper_inclusive = false): BetweenRange
{
    return new BetweenRange($lower_bound, $upper_bound, $upper_inclusive);
}
