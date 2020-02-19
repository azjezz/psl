<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Retrieve a value from the array using the given key.
 * If the key doesn't exist, an InvariantViolationException will be thrown.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 * @psalm-param Tk $key
 *
 * @psalm-return Tv
 *
 * @psalm-suppress MissingReturnType
 */
function at(array $array, $key)
{
    Psl\invariant(contains_key($array, $key), 'Key (%s) is out-of-bound.', (string) $key);

    /** @psalm-var Tv */
    return $array[$key];
}
