<?php

declare(strict_types=1);

namespace Psl\Math;

/**
 * Returns the integer sum of the values of the given iterable.
 *
 * @psalm-param list<int> $numbers
 *
 * @psalm-pure
 */
function sum(array $numbers): int
{
    $result = 0;
    foreach ($numbers as $number) {
        $result += $number;
    }

    return $result;
}
