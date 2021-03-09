<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Dict;

/**
 * Take the first n elements from an array.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param array<Tk, Tv> $array
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\take` instead.
 * @see Dict\take()
 */
function take(iterable $array, int $n): array
{
    return Dict\take($array, $n);
}
