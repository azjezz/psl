<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Filter out null values from the given array.
 *
 * Example:
 *      Arr\filter_nulls([1, null, 5])
 *      => Arr(1, 5)
 *
 * @psalm-template T
 *
 * @psalm-param list<T|null> $array
 *
 * @psalm-return list<T>
 *
 * @psalm-pure
 */
function filter_nulls(array $array): array
{
    /** @psalm-var list<T> $result */
    $result = [];
    foreach ($array as $value) {
        if (null !== $value) {
            $result[] = $value;
        }
    }

    return $result;
}
