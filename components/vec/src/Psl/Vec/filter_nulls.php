<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_filter;
use function array_values;
use function is_array;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Vec\filter_nulls([1, null, 5])
 *      => Vec(1, 5)
 *
 * @template T
 *
 * @param iterable<T|null> $iterable
 *
 * @return list<T>
 */
function filter_nulls(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_values(array_filter($iterable, static fn(mixed $value): bool => null !== $value));
    }

    $result = [];
    foreach ($iterable as $v) {
        if (null === $v) {
            continue;
        }

        $result[] = $v;
    }

    return $result;
}
