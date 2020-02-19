<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Get the first value of an array, If the array is empty, null will be returned.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return null|Tv
 *
 * @psalm-pure
 *
 * @psalm-suppress MissingReturnType
 */
function first(array $array)
{
    /** @psalm-var null|Tk $first */
    $first = first_key($array);

    /** @psalm-var null|Tv */
    return null !== $first ? at($array, $first) : null;
}
