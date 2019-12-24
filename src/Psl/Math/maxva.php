<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest of all input numbers.
 *
 * @psalm-template T as int|float
 *
 * @psalm-param T $first
 * @psalm-param T $second
 * @psalm-param T ...$rest
 *
 * @psalm-return T
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
