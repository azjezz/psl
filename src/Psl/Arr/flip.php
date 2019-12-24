<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Flips the keys and values of an iterable. In case of
 * duplicate values, later keys will overwrite the previous ones.
 *
 * Examples:
 *
 *      Iter\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Iter(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv as array-key
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return array<Tv, Tk>
 */
function flip(array $array): array
{
    $result = [];
    foreach ($array as $k => $v) {
        $result[$v] = $k;
    }

    /** @psalm-var array<Tv, Tk> $result*/
    return $result;
}
