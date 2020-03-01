<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Get the last key of an array, if the array is empty, null will be returned.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return null|Tk
 *
 * @psalm-pure
 */
function last_key(array $array)
{
    /** @psalm-var null|Tk */
    return \array_key_last($array);
}
