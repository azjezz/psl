<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_filter;
use function is_array;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Dict\filter_nulls([1, null, 5])
 *      => Dict(0 => 1, 2 => 5)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv|null> $iterable
 *
 * @return array<Tk, Tv>
 */
function filter_nulls(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_filter($iterable, static fn(mixed $value): bool => null !== $value);
    }

    $result = [];
    foreach ($iterable as $k => $v) {
        if (null === $v) {
            continue;
        }

        $result[$k] = $v;
    }

    return $result;
}
