<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest number of all the given numbers.
 *
 * @param T $first
 * @param T $second
 * @param T ...$rest
 *
 * @return T
 *
 * @pure
 *
 * @api
 */
function maxva<T : int|float>(int|float $first, int|float $second, int|float ...$rest): int|float
{
    $max = $first > $second ? $first : $second;
    foreach ($rest as $number) {
        if ($number <= $max) {
            continue;
        }

        $max = $number;
    }

    return $max;
}
