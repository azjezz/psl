<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Get the first value of an array, If the array is empty, null will be returned.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 *
 * @return Tv|null
 *
 * @pure
 *
 * @deprecated use `Iter\first()` instead.
 * @see Iter\first()
 */
function first(array $array)
{
    /**
     * @psalm-suppress DeprecatedFunction
     */
    $first = first_key($array);

    if (null === $first) {
        return null;
    }

    return $array[$first];
}
