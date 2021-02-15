<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;
use Psl\Iter;
use Psl\Vec;

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
 * @return array<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If $keys and $values have different length.
 */
function associate(iterable $keys, iterable $values): array
{
    $key_vec = Vec\values($keys);
    $value_vec = Vec\values($values);

    Psl\invariant(
        Iter\count($key_vec) === Iter\count($value_vec),
        'Expected length of $keys and $values to be the same',
    );

    $result = [];
    foreach ($key_vec as $i => $key) {
        $result[$key] = $value_vec[$i];
    }
    return $result;
}
