<?php

declare(strict_types=1);

namespace Psl\Dict;

use function array_key_exists;

/**
 * Returns a new dict containing only the keys found in both the input array
 * and the given list.
 *
 * The dict will have the same ordering as the `$keys` iterable.
 *
 * @param iterable<Tk, Tv> $iterable
 * @param iterable<Tk> $keys
 *
 * @return array<Tk, Tv>
 *
 * @api
 */
function select_keys<Tk: string|int, Tv>(iterable $iterable, iterable $keys): array
{
    $array = [];
    foreach ($iterable as $k => $v) {
        $array[$k] = $v;
    }

    $result = [];
    foreach ($keys as $key) {
        if (!array_key_exists($key, $array)) {
            continue;
        }

        $result[$key] = $array[$key];
    }

    return $result;
}
