<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the smallest element of the given list, or null if the
 * list is empty.
 *
 * @template T of int|float
 *
 * @param list<T> $numbers
 *
 * @return T|null
 *
 * @pure
 */
function min(array $numbers): null|float|int
{
    $min = null;
    foreach ($numbers as $number) {
        if (null === $min || $number < $min) {
            $min = $number;
        }
    }

    return $min;
}
