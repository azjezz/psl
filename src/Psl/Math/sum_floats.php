<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the float sum of the values of the given iterable.
 *
 * @param list<int|float> $numbers
 *
 * @pure
 */
function sum_floats(array $numbers): float
{
    $result = 0.0;
    foreach ($numbers as $number) {
        $result += (float)$number;
    }

    return $result;
}
