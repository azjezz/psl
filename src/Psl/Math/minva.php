<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the smallest of all input numbers.
 *
 * @template T of int|float
 *
 * @param T $first
 * @param T $second
 * @param T ...$rest
 *
 * @return T
 *
 * @psalm-pure
 */
function minva($first, $second, ...$rest)
{
    $min = $first < $second ? $first : $second;
    foreach ($rest as $number) {
        if ($number < $min) {
            $min = $number;
        }
    }

    return $min;
}
