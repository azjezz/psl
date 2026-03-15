<?php

declare(strict_types=1);

namespace Psl\Math;

use Closure;

/**
 * Returns the smallest element of the given iterable, or null if the
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
function min_by(iterable $numbers, Closure $numericFunction): mixed
{
    $min = null;
    $minNum = null;
    foreach ($numbers as $value) {
        $valueNum = $numericFunction($value);
        if (null === $minNum || $valueNum <= $minNum) {
            $min = $value;
            $minNum = $valueNum;
        }
    }

    return $min;
}
