<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Get the last value of an array, if the array is empty, returns null.
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
 * @deprecated use `Iter\last` instead.
 *
 * @see Iter\last()
 */
function last(array $array)
{
    /**
     * @psalm-var Tk|null $last
     * @psalm-suppress DeprecatedFunction
     */
    $last = last_key($array);
    if (null === $last) {
        return null;
    }

    return $array[$last];
}
