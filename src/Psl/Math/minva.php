<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the smallest of all input numbers.
 *
 * @psalm-template T of int|float
 *
 * @psalm-param T $first
 * @psalm-param T $second
 * @psalm-param T ...$rest
 *
 * @psalm-return T
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
