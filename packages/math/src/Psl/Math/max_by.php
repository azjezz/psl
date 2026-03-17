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
 * @template T
 *
 * @param iterable<T> $numbers
 * @param (Closure(T): numeric) $numericFunction
 *
 * @return T|null
 */
function max_by(iterable $numbers, Closure $numericFunction): mixed
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
