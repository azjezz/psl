<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the smallest number of all the given numbers.
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
function minva(int|float $first, int|float $second, int|float ...$rest): int|float
{
    $min = $first < $second ? $first : $second;
    foreach ($rest as $number) {
        if ($number < $min) {
            $min = $number;
        }
    }

    return $min;
}
