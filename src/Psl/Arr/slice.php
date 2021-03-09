<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Dict;

/**
 * Takes a slice from an array.
 *
 * Examples:
 *
 *      Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => Arr(0, 1, 2, 3, 4, 5)
 *
 *      Arr\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Arr(0, 1, 2)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk,Tv> $array Array to take the slice from
 * @param int $start Start offset
 * @param int $length Length (if not specified all remaining values from the array are used)
 *
 * @throws Psl\Exception\InvariantViolationException If the $start offset or $length are negative
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\slice()` instead.
 * @see Dict\slice()
 */
function slice(array $array, int $start, ?int $length = null): array
{
    return Dict\slice($array, $start, $length);
}
