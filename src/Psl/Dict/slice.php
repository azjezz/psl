<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;

/**
 * Takes a slice from an iterable.
 *
 * Examples:
 *
 *      Dict\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5)
 *      => Dict(0, 1, 2, 3, 4, 5)
 *
 *      Dict\slice([-5, -4, -3, -2, -1, 0, 1, 2, 3, 4, 5], 5, 3)
 *      => Dict(0, 1, 2)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk,Tv> $iterable   Iterable to take the slice from
 * @psalm-param int             $start      Start offset
 * @psalm-param int             $length     Length (if not specified all remaining values from the array are used)
 *
 * @psalm-return array<Tk, Tv>
 *
 * @throws Psl\Exception\InvariantViolationException If the $start offset or $length are negative
 */
function slice(iterable $iterable, int $start, ?int $length = null): array
{
    Psl\invariant($start >= 0, 'Start offset must be non-negative.');
    Psl\invariant(null === $length || $length >= 0, 'Length must be non-negative.');

    $result = [];
    if (0 === $length) {
        return $result;
    }

    $i = 0;
    foreach ($iterable as $key => $value) {
        if ($i++ < $start) {
            continue;
        }

        $result[$key] = $value;
        if (null !== $length && $i >= $start + $length) {
            break;
        }
    }

    return $result;
}
