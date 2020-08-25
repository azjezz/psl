<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the largest element of the given iterable, or null if the
 * iterable is empty.
 *
 * @psalm-template T of int|float
 *
 * @psalm-param list<T> $numbers
 *
 * @psalm-return null|T
 *
 * @psalm-pure
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
