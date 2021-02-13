<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl;

/**
 * Produce a list of values generated using the given factory.
 *
 * Example:
 *
 *     Vec\reproduce(fn(int $i): int => $i * 2, 5)
 *     => Vec(2, 4, 6, 8, 10)
 *
 * @template T
 *
 * @param (callable(int): T) $factory
 *
 * @return list<T>
 *
 * @throws Psl\Exception\InvariantViolationException If $size is lower than 1.
 */
function reproduce(int $size, callable $factory): array
{
    Psl\invariant($size >= 1, 'The number of times you want to reproduce must be at least 1.');

    $result = [];
    for ($i = 1; $i <= $size; $i++) {
        $result[] = $factory($i);
    }

    return $result;
}
