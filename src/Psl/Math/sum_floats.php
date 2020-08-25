<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the float sum of the values of the given iterable.
 *
 * @psalm-param iterable<numeric> $numbers
 *
 * @psalm-pure
 */
function sum_floats(iterable $numbers): float
{
    $result = 0.0;
    foreach ($numbers as $number) {
        $result += $number;
    }

    return (float) $result;
}
