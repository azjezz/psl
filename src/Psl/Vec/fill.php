<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_fill;

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
    return array_fill(0, $size, $value);
}
