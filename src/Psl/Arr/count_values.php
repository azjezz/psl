<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array mapping each value to the number of times it appears
 * in the given array.
 *
 * @psalm-template T of array-key
 *
 * @psalm-param iterable<T> $values
 *
 * @psalm-return array<T, int>
 *
 * @deprecated use `Dict\count_values` instead.
 *
 * @see Dict\count_values()
 */
function count_values(iterable $values): array
{
    return Dict\count_values($values);
}
