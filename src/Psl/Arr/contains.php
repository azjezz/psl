<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Iter;

/**
 * Returns true if the given array contains the value. Strict equality is
 * used.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param Tv $value
 *
 * @pure
 *
 * @deprecated use `Iter\contains` instead.
 * @see Iter\contains()
 */
function contains(array $array, $value): bool
{
    foreach ($array as $v) {
        if ($value === $v) {
            return true;
        }
    }

    return false;
}
