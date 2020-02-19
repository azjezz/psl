<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array containing only the keys found in both the input array
 * and the given iterable. The array will have the same ordering as the
 * `$keys` iterable.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>   $array
 * @psalm-param iterable<Tk>    $keys
 *
 * @param array $keys
 *
 * @return mixed[]
 *
 * @psalm-return array<Tk, Tv>
 */
function select_keys(array $array, iterable $keys): array
{
    $result = [];
    foreach ($keys as $key) {
        if (contains_key($array, $key)) {
            $result[$key] = $array[$key];
        }
    }

    return $result;
}
