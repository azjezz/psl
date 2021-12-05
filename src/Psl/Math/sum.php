<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the sum of all the given numbers.
 *
 * @param list<int> $numbers
 *
 * @pure
 */
function sum(array $numbers): int
{
    $result = 0;
    foreach ($numbers as $number) {
        $result += $number;
    }

    return $result;
}
