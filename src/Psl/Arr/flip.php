<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Flips the keys and values of an array. In case of
 * duplicate values, later keys will overwrite the previous ones.
 *
 * Examples:
 *
 *      Arr\flip(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Arr(1 => 'a', 2 => 'b', 3 => 'c')
 *
 * @template Tk of array-key
 * @template Tv of array-key
 *
 * @param array<Tk, Tv> $array
 *
 * @return array<Tv, Tk>
 *
 * @deprecated use `Dict\flip` instead.
 * @see Dict\flip()
 */
function flip(array $array): array
{
    return Dict\flip($array);
}
