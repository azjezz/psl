<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Iter;

/**
 * Return the arithmetic mean of the numbers in the given iterable.
 *
 * @param iterable<int|float> $numbers
 */
function mean(iterable $numbers): ?float
{
    $count = (float) Iter\count($numbers);
    if (0.0 === $count) {
        return null;
    }

    $mean = 0.0;
    foreach ($numbers as $number) {
        $mean += (float)$number / $count;
    }

    return $mean;
}
