<?php

declare(strict_types=1);

namespace Psl\Math;

use function array_sum;

/**
 * Returns the sum of all the given numbers.
 *
 * @param list<int|float> $numbers
 *
 * @pure
 */
function sum_floats(array $numbers): float
{
    return (float) array_sum($numbers);
}
