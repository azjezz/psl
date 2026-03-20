<?php

declare(strict_types=1);

namespace Psl\Math;

use function count;
use function sort;

/**
 * Returns the median of the given numbers in the list.
 *
 * Returns null if the given iterable is empty.
 *
 * @param list<int|float> $numbers
 *
 * @return ($numbers is non-empty-list ? float : null)
 *
 * @pure
 */
function median(array $numbers): float|null
{
    sort($numbers);
    $count = count($numbers);
    if (0 === $count) {
        return null;
    }

    /** @var int<0, max> $middleIndex */
    $middleIndex = namespace\div($count, 2);
    if (0 === ($count % 2)) {
        /** @var int<1, max> $middleIndex */
        return namespace\mean([$numbers[$middleIndex], $numbers[$middleIndex - 1]]);
    }

    return (float) $numbers[$middleIndex];
}
