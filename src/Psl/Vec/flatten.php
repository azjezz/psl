<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Returns a new list formed by flattening a list of lists into a single list.
 *
 * Examples:
 *
 *     Vec\flatten([[1, 2], [3, 4], [5]])
 *     => Vec(1, 2, 3, 4, 5)
 *
 *     Vec\flatten([['a', 'b'], [], ['c']])
 *     => Vec('a', 'b', 'c')
 *
 *     Vec\flatten([])
 *     => Vec()
 *
 * @template T
 *
 * @param iterable<iterable<T>> $iterables
 *
 * @return list<T>
 */
function flatten(iterable $iterables): array
{
    $result = [];
    foreach ($iterables as $iterable) {
        foreach ($iterable as $value) {
            $result[] = $value;
        }
    }

    return $result;
}
