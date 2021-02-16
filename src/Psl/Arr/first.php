<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Get the first value of an array, If the array is empty, null will be returned.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return Tv|null
 *
 * @psalm-pure
 *
 * @deprecated use `Iter\first()` instead.
 *
 * @see Iter\first()
 */
function first(array $array)
{
    /**
     * @psalm-var Tk|null $first
     * @psalm-suppress DeprecatedFunction
     */
    $first = first_key($array);

    if (null === $first) {
        return null;
    }

    return $array[$first];
}
