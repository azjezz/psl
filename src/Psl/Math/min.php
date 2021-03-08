<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the smallest element of the given Traversable, or null if the
 * Traversable is empty.
 *
 * @template T of int|float
 *
 * @param list<T> $numbers
 *
 * @return T|null
 *
 * @pure
 */
function min(array $numbers)
{
    $min = null;
    foreach ($numbers as $number) {
        if (null === $min || $number < $min) {
            $min = $number;
        }
    }

    return $min;
}
