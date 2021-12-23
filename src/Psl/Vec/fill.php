<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Returns a new vec of size `$size` where all the values are `$value`.
 *
 * If you need a range of items not repeats, use `Vec\range(0, $n - 1)`.
 *
 * @template T
 *
 * @param int<0, max> $size
 * @param T $value
 *
 * @return list<T>
 *
 * @pure
 */
function fill(int $size, mixed $value): array
{
    $result = [];
    for ($i = 0; $i < $size; $i++) {
        $result[] = $value;
    }

    return $result;
}
