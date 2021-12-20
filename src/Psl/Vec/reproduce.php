<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Produce a list of values generated using the given factory.
 *
 * Example:
 *
 *     Vec\reproduce(5, fn(int $i): int => $i * 2)
 *     => Vec(2, 4, 6, 8, 10)
 *
 * @template T
 *
 * @param positive-int $size
 * @param (callable(int): T) $factory
 *
 * @return list<T>
 */
function reproduce(int $size, callable $factory): array
{
    $result = [];
    for ($i = 1; $i <= $size; $i++) {
        $result[] = $factory($i);
    }

    return $result;
}
