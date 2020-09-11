<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Arr\filter_nulls([1, null, 5])
 *      => Arr(1, 5)
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T|null> $iterable
 *
 * @psalm-return list<T>
 */
function filter_nulls(iterable $iterable): array
{
    /** @psalm-var list<T> $result */
    $result = [];
    foreach ($iterable as $value) {
        if (null !== $value) {
            $result[] = $value;
        }
    }

    return $result;
}
