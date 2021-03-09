<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns the value at an index of an array.
 *
 * This function simplifies the common pattern of checking for an index in an array and
 * selecting a default value if it does not exist.
 *
 * You should NOT use idx as a general replacement for accessing array indices.
 *
 * idx is used to look for an index in an array, and return either the value at that index (if it exists)
 * or some default (if it does not). Without idx, you need to do this:
 *
 * ```
 *   Arr\contains_key($array, $index) ? $array[$index] : $default;
 * ```
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param Tk $index
 * @param Tv $default
 *
 * @return Tv
 *
 * @pure
 *
 * @deprecated use `$array[$index] ?? $default` instead.
 */
function idx(array $array, $index, $default = null)
{
    /** @psalm-suppress DeprecatedFunction */
    if (contains_key($array, $index)) {
        return $array[$index];
    }

    return $default;
}
