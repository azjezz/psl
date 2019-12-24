<?php

declare(strict_types=1);

namespace Psl\Math;

use Psl\Iter;

/**
 * Return the arithmetic mean of the numbers in the given iterable.
 *
 * @psalm-param iterable<numeric> $numbers
 */
function mean(iterable $numbers): ?float
{
    $count = (float) Iter\count($numbers);
    if (0.0 === $count) {
        return null;
    }

    $mean = 0.0;
    foreach ($numbers as $number) {
        $mean += $number / $count;
    }

    return (float) $mean;
}
