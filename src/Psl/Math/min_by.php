<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the smallest element of the given iterable, or null if the
 * iterable is empty.
 *
 * The value for comparison is determined by the given function. In the case of
 * duplicate numeric keys, later values overwrite previous ones.
 *
 * Example:
 *
 *      Math\min_by(['a', 'ل'], fn($value) => Str\Byte\length($value))
 *      => Str('a')
 *
 *      Math\min_by(['a', 'b'], fn($value) => Str\Byte\length($value))
 *      => Str('b')
 *
 * @template T
 *
 * @param iterable<T> $values
 * @param (callable(T): numeric) $num_func
 *
 * @return T|null
 */
function min_by(iterable $values, callable $num_func)
{
    $min     = null;
    $min_num = null;
    foreach ($values as $value) {
        $value_num = $num_func($value);
        if (null === $min_num || $value_num <= $min_num) {
            $min     = $value;
            $min_num = $value_num;
        }
    }

    return $min;
}
