<?php

declare(strict_types=1);

namespace Psl\Arr;

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
 */
function last(array $array)
{
    /** @psalm-var Tk|null $last */
    $last = last_key($array);
    if (null === $last) {
        return null;
    }

    /** @psalm-suppress MissingThrowsDocblock - we are sure that $last is within-bounds. */
    return at($array, $last);
}
