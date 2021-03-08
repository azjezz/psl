<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest element of the given iterable, or null if the
 * iterable is empty.
 *
 * @template T of int|float
 *
 * @param list<T> $numbers
 *
 * @return T|null
 *
 * @pure
 */
function max(array $numbers)
{
    $max = null;
    foreach ($numbers as $number) {
        if (null === $max || $number > $max) {
            $max = $number;
        }
    }

    return $max;
}
