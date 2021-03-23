<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Get the last value of an array, if the array is empty, returns null.
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
 * @deprecated use `Iter\last` instead.
 * @see Iter\last()
 */
function last(array $array)
{
    /**
     * @psalm-suppress DeprecatedFunction
     */
    $last = last_key($array);
    if (null === $last) {
        return null;
    }

    return $array[$last];
}
