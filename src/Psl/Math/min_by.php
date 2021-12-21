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
 * @param (Closure(T): numeric) $numeric_function
 *
 * @return T|null
 */
function min_by(iterable $numbers, Closure $numeric_function): mixed
{
    $min     = null;
    $min_num = null;
    foreach ($numbers as $value) {
        $value_num = $numeric_function($value);
        if (null === $min_num || $value_num <= $min_num) {
            $min     = $value;
            $min_num = $value_num;
        }
    }

    return $min;
}
