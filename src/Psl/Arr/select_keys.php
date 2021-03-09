<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a new array containing only the keys found in both the input array
 * and the given list. The array will have the same ordering as the
 * `$keys` list.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 * @param list<Tk> $keys
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\select_keys` instead.
 * @see Dict\select_keys()
 */
function select_keys(array $array, array $keys): array
{
    return Dict\select_keys($array, $keys);
}
