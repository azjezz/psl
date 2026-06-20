<?php

declare(strict_types=1);

namespace Psl\Math;

use Closure;

/**
 * Returns the largest element of the given iterable, or null if the
 * iterable is empty.
 *
 * The value for comparison is determined by the given function.
 *
 * In the case of duplicate values, later values overwrite previous ones.
 *
 * @param iterable<T> $numbers
 * @param (Closure(T): numeric) $numericFunction
 *
 * @api
 */
function max_by<T>(iterable $numbers, Closure $numericFunction): T|null
{
    $max = null;
    $maxNum = null;
    foreach ($numbers as $value) {
        $valueNum = $numericFunction($value);
        if (null === $maxNum || $valueNum >= $maxNum) {
            $max = $value;
            $maxNum = $valueNum;
        }
    }

    return $max;
}
