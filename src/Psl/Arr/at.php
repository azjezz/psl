<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Retrieve a value from the array using the given key.
 * If the key doesn't exist, an InvariantViolationException will be thrown.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param Tk $key
 *
 * @throws Psl\Exception\InvariantViolationException If $key is out-of-bounds.
 *
 * @return Tv
 *
 * @pure
 *
 * @deprecated use `$array[$key]` instead.
 */
function at(array $array, $key)
{
    /** @psalm-suppress DeprecatedFunction */
    Psl\invariant(contains_key($array, $key), 'Key (%s) is out-of-bounds.', $key);

    /** @var Tv */
    return $array[$key];
}
