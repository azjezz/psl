<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Iter;
use Psl\Str;

/**
 * Returns a new array mapping each value to the number of times it appears
 * in the given iterable.
 *
 * @psalm-template T of array-key
 *
 * @psalm-param iterable<T> $values
 *
 * @psalm-return array<T, int>
 * @return int[]
 */
function count_values(iterable $values): array
{
    /** @psalm-var list<T> $values */
    $values = Iter\to_array($values);
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
