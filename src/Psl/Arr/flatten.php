<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array formed by merging the iterable elements of the
 * given iterable. In the case of duplicate keys, later values will overwrite
 * the previous ones.
 *
 * Example:
 *      Arr\flatten([[1, 2], [9, 8]])
 *      => Arr(0 => 9, 1 => 8)
 *
 *      Arr\flatten([[0 => 1, 1 => 2], [2 => 9, 3 => 8]])
 *      => Arr(0 => 1, 1 => 2, 2 => 9, 3 => 8)
 *
 * @psalm-template Tk as array-key
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
