<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array containing only the keys found in both the input array
 * and the given list. The array will have the same ordering as the
 * `$keys` list.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>   $array
 * @psalm-param list<Tk>        $keys
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function select_keys(array $array, array $keys): array
{
    $result = [];
    foreach ($keys as $key) {
        if (contains_key($array, $key)) {
            $result[$key] = $array[$key];
        }
    }

    return $result;
}
