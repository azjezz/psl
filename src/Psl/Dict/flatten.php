<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_replace;
use function is_array;

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
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<iterable<Tk, Tv>> $iterables
 *
 * @return array<Tk, Tv>
 */
function flatten(iterable $iterables): array
{
    if (is_array($iterables)) {
        $allArrays = true;
        foreach ($iterables as $inner) {
            if (is_array($inner)) {
                continue;
            }

            $allArrays = false;
            break;
        }

        if ($allArrays) {
            /** @var array<array<Tk, Tv>> $iterables */
            return [] === $iterables ? [] : array_replace(...$iterables);
        }
    }

    $result = [];
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            $result[$key] = $value;
        }
    }

    return $result;
}
