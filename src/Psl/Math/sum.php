<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the integer sum of the values of the given iterable.
 *
 * @psalm-param iterable<int> $numbers
 *
 * @psalm-pure
 */
function sum(iterable $numbers): int
{
    $result = 0;
    foreach ($numbers as $number) {
        $result += $number;
    }

    return $result;
}
