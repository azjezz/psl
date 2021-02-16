<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;
use Psl\Type;

/**
 * Returns a new dict mapping each value to the number of times it appears
 * in the given array.
 *
 * @psalm-template T of array-key
 *
 * @psalm-param iterable<T> $values
 *
 * @psalm-return array<T, int>
 */
function count_values(iterable $values): array
{
    /** @psalm-var array<T, int> $result */
    $result = [];

    foreach ($values as $value) {
        Psl\invariant(
            Type\is_arraykey($value),
            'Expected all values to be of type array-key, value of type (%s) provided.',
            gettype($value)
        );

        /** @psalm-var int $count */
        $count = $result[$value] ?? 0;
        /** @psalm-var T $value */
        $result[$value] = $count + 1;
    }

    return $result;
}
