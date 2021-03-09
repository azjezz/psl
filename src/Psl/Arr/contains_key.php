<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

use function array_key_exists;

/**
 * Returns true if the given array contains the key.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param Tk $key
 *
 * @pure
 *
 * @deprecated use `Iter\contains_key()` instead.
 * @see Iter\contains_key()
 */
function contains_key(array $array, $key): bool
{
    return array_key_exists($key, $array);
}
