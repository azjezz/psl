<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_combine;
use function count;
use function is_array;
use function iterator_to_array;

/**
 * Returns a new dict where each element in `$keys` maps to the
 * corresponding element in `$values`.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk> $keys
 * @param iterable<Tv> $values
 *
 * @throws Exception\LogicException If $keys and $values have different length.
 *
 * @return array<Tk, Tv>
 */
function associate(iterable $keys, iterable $values): array
{
    if (!is_array($keys)) {
        $keys = iterator_to_array($keys);
    }

    if (!is_array($values)) {
        $values = iterator_to_array($values);
    }

    $keysCount = count($keys);
    if (count($values) !== $keysCount) {
        throw new Exception\LogicException('Expected length of $keys and $values to be the same');
    }

    if (0 === $keysCount) {
        return [];
    }

    return array_combine($keys, $values);
}
