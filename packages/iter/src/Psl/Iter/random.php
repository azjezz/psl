<?php

declare(strict_types=1);

namespace Psl\Iter;

use function array_values;
use function is_array;
use function iterator_to_array;
use function mt_rand;

/**
 * Retrieve a random value from a non-empty iterable.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @throws Exception\InvalidArgumentException If $iterable is empty.
 *
 * @return T
 */
function random(iterable $iterable): mixed
{
    // We convert the iterable to an array before checking if it is empty,
    // this helps us avoids an issue when the iterable is a generator where
    // would exhaust it when calling `count`
    $values = is_array($iterable) ? array_values($iterable) : array_values(iterator_to_array($iterable));
    if ([] === $values) {
        throw new Exception\InvalidArgumentException('Expected a non-empty iterable.');
    }

    $size = \count($values);
    if (1 === $size) {
        return $values[0];
    }

    $i = mt_rand(0, $size - 1);
    return $values[$i];
}
