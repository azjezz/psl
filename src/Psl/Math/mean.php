<?php

declare(strict_types=1);

namespace Psl\Math;

use function count;

/**
 * Returns the arithmetic mean of the given numbers in the list.
 *
 * Return null if the given list is empty.
 *
 * @param list<int|float> $numbers
 *
 * @return ($numbers is non-empty-list ? float : null)
 *
 * @pure
 */
function mean(array $numbers): float|null
{
    $count = (float) count($numbers);
    if (0.0 === $count) {
        return null;
    }

    $mean = 0.0;
    foreach ($numbers as $number) {
        $mean += ((float) $number) / $count;
    }

    return $mean;
}
