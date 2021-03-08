<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;
use Psl\Type;

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
    /** @var array<T, int> $result */
    $result = [];

    foreach ($values as $value) {
        Psl\invariant(
            Type\array_key()->matches($value),
            'Expected all values to be of type array-key, value of type (%s) provided.',
            gettype($value)
        );

        /** @var int $count */
        $count = $result[$value] ?? 0;
        /** @var T $value */
        $result[$value] = $count + 1;
    }

    return $result;
}
