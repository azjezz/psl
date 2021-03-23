<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest of all input numbers.
 *
 * @template T of int|float
 *
 * @param T $first
 * @param T $second
 * @param T ...$rest
 *
 * @return T
 *
 * @pure
 */
function maxva($first, $second, ...$rest)
{
    $max = $first > $second ? $first : $second;
    foreach ($rest as $number) {
        if ($number > $max) {
            $max = $number;
        }
    }

    return $max;
}
