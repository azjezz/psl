<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a dict where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk>        $keys
 * @psalm-param (callable(Tk): Tv)  $value_func
 *
 * @psalm-return array<Tk, Tv>
 */
function from_keys(iterable $keys, callable $value_func): array
{
    $result = [];
    foreach ($keys as $key) {
        $result[$key] = $value_func($key);
    }

    return $result;
}
