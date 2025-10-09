<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Vec;

use function count;

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
        $keys = Vec\values($keys);
    }

    if (!is_array($values)) {
        $values = Vec\values($values);
    }

    $keys_count = count($keys);
    if (count($values) !== $keys_count) {
        throw new Exception\LogicException('Expected length of $keys and $values to be the same');
    }

    if (0 === $keys_count) {
        return [];
    }

    return array_combine($keys, $values);
}
