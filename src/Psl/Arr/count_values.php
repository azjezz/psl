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
 * @psalm-template Tv of array-key
 *
 * @psalm-param iterable<Tv> $values
 *
 * @psalm-return array<Tv, int>
 * @return int[]
 */
function count_values(iterable $values): array
{
    /** @psalm-var list<Tv> $values */
    $values = Iter\to_array($values);
    /** @psalm-var array<Tv, int> $result */
    $result = [];

    foreach ($values as $value) {
        /** @psalm-var int $count */
        $count = idx($result, $value, 0);
        $result[$value] = $count + 1;
    }

    return $result;
}
