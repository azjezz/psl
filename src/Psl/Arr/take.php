<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;
use Psl\Dict;

/**
 * Take the first n elements from an array.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv> $array
 *
 * @psalm-return array<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $n is negative
 *
 * @deprecated use `Dict\take` instead.
 *
 * @see Dict\take()
 */
function take(iterable $array, int $n): array
{
    return Dict\take($array, $n);
}
