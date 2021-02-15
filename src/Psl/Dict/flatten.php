<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a new dict formed by merging the iterable elements of the
 * given iterable.
 *
 * In the case of duplicate keys, later values will overwrite
 * the previous ones.
 *
 * Example:
 *      Dict\flatten([[1, 2], [9, 8]])
 *      => Dict(0 => 9, 1 => 8)
 *
 *      Dict\flatten([[0 => 1, 1 => 2], [2 => 9, 3 => 8]])
 *      => Dict(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<iterable<Tk, Tv>> $iterables
 *
 * @psalm-return array<Tk, Tv>
 */
function flatten(iterable $iterables): array
{
    $result = [];
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            $result[$key] = $value;
        }
    }

    return $result;
}
