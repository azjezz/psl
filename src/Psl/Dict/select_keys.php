<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl\Iter;

/**
 * Returns a new dict containing only the keys found in both the input array
 * and the given list.
 *
 * The dict will have the same ordering as the `$keys` iterable.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param iterable<Tk> $keys
 *
 * @return array<Tk, Tv>
 */
function select_keys(iterable $iterable, iterable $keys): array
{
    $array = [];
    foreach ($iterable as $k => $v) {
        $array[$k] = $v;
    }

    $result = [];
    foreach ($keys as $key) {
        if (Iter\contains_key($array, $key)) {
            $result[$key] = $array[$key];
        }
    }

    return $result;
}
