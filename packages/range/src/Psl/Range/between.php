<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
 *
 * @psalm-mutation-free
 */
function between(int $lowerBound, int $upperBound, bool $upperInclusive = false): BetweenRange
{
    return new BetweenRange($lowerBound, $upperBound, $upperInclusive);
}
