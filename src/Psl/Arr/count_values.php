<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns a new array mapping each value to the number of times it appears
 * in the given iterable.
 *
 * @psalm-template Tv as array-key
 *
 * @psalm-param iterable<Tv> $values
 *
 * @psalm-return array<Tv, int>
 *
 * @psalm-suppress InvalidReturnStatement
 * @psalm-suppress InvalidReturnType
 */
function count_values(iterable $values): array
{
    $values = Iter\to_array($values);
    /** @psalm-var array<Tv, int> $result */
    $result = [];

    /** @psalm-var Tv $value */
    foreach ($values as $value) {
        /**
         * @psalm-var int
         * @psalm-suppress InvalidArgument
         */
        $result[$value] = idx($result, $value, 0) + 1;
    }

    return $result;
}
