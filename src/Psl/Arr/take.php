<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl;

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
 * @psalm-pure
 */
function take(array $array, int $n): array
{
    return slice($array, 0, $n);
}
