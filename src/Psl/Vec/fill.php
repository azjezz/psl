<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl;

/**
 * Returns a new vec of size `$size` where all the values are `$value`.
 *
 * If you need a range of items not repeats, use `Vec\range(0, $n - 1)`.
 *
 * @template T
 *
 * @param T $value
 *
 * @throws Psl\Exception\InvariantViolationException If $size is negative.
 *
 * @return list<T>
 *
 * @pure
 */
function fill(int $size, $value): array
{
    Psl\invariant($size >= 0, 'Expected non-negative fill size, got %d.', $size);

    $result = [];
    for ($i = 0; $i < $size; $i++) {
        $result[] = $value;
    }

    return $result;
}
