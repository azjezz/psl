<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest element of the given iterable, or null if the
 * iterable is empty.
 *
 * The value for comparison is determined by the given function in the case of
 * duplicate numeric keys, later values overwrite the previous ones.
 *
 * @template T
 *
 * @param iterable<T> $values
 * @param (callable(T): numeric) $num_func
 *
 * @return T|null
 */
function max_by(iterable $values, callable $num_func)
{
    $max     = null;
    $max_num = null;
    foreach ($values as $value) {
        $value_num = $num_func($value);
        if (null === $max_num || $value_num >= $max_num) {
            $max     = $value;
            $max_num = $value_num;
        }
    }

    return $max;
}
