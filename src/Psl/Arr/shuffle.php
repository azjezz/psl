<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

/**
 * Shuffle the given array.
 *
 * Example:
 *
 *      Arr\shuffle([1, 2, 3])
 *      => Arr(2, 3, 1)
 *
 *      Arr\shuffle('a' => 1, 'b' => 2, 'c' => 3)
 *      => Arr(2, 3, 1)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return array<int, Tv> the shuffled array.
 */
function shuffle(array $array): array
{
    $shuffled = \shuffle($array);
    Psl\invariant($shuffled, 'Unexpected error');

    return $array;
}
