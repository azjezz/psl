<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Flips the keys and values of an array. In case of
 * duplicate values, later keys will overwrite the previous ones.
 *
 * Examples:
 *
 *      Iter\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Iter(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv of array-key
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return array<Tv, Tk>
 */
function flip(array $array): array
{
    $result = [];
    foreach ($array as $k => $v) {
        Psl\invariant(is_arraykey($v), 'Expected all values to be of type array-key, value of type (%s) provided.', gettype($v));
        $result[$v] = $k;
    }

    /** @psalm-var array<Tv, Tk> $result*/
    return $result;
}
