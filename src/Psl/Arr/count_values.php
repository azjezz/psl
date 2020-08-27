<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Returns a new array mapping each value to the number of times it appears
 * in the given array.
 *
 * @psalm-template T of array-key
 *
 * @psalm-param list<T> $values
 *
 * @psalm-return array<T, int>
 *
 * @psalm-pure
 */
function count_values(array $values): array
{
    /** @psalm-var array<T, int> $result */
    $result = [];

    foreach ($values as $value) {
        Psl\invariant(is_arraykey($value), 'Expected all values to be of type array-key, value of type (%s) provided.', gettype($value));
        /** @psalm-var int $count */
        $count = idx($result, $value, 0);
        /** @psalm-var T $value */
        $result[$value] = $count + 1;
    }

    return $result;
}
