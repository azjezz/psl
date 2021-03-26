<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Vec;

use function array_count_values;
use function is_array;

/**
 * Returns a new dict mapping each value to the number of times it appears
 * in the given array.
 *
 * @template T of array-key
 *
 * @param iterable<T> $values
 *
 * @return array<T, int>
 */
function count_values(iterable $values): array
{
    if (!is_array($values)) {
        $values = Vec\values($values);
    }

    return array_count_values($values);
}
