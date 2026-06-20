<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest number of all the given numbers.
 *
 * @pure
 *
 * @api
 */
function maxva<T: int|float>(T $first, T $second, T ...$rest): T
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
