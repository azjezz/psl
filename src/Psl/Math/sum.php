<?php

declare(strict_types=1);

namespace Psl\Math;

use function array_sum;

/**
 * Returns the sum of all the given numbers.
 *
 * @param list<int> $numbers
 *
 * @pure
 */
function sum(array $numbers): int
{
    return (int) array_sum($numbers);
}
