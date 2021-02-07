<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * Shuffle the given array.
 *
 * Example:
 *
 *      Arr\shuffle([1, 2, 3])
 *      => Arr(2, 3, 1)
 *
 *      Arr\shuffle(['a' => 1, 'b' => 2, 'c' => 3])
 *      => Arr(2, 3, 1)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return list<Tv> the shuffled array.
 *
 * @deprecated since 1.2, use Vec\shuffle instead.
 *
 * @see Vec\shuffle()
 */
function shuffle(array $array): array
{
    return Vec\shuffle($array);
}
