<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

use function array_fill;

/**
 * Fill an array with values.
 *
 * @template T
 *
 * @param T $value
 *
 * @return array<int, T>
 *
 * @pure
 *
 * @deprecated use `Vec\fill` instead.
 * @see Vec\fill()
 */
function fill(mixed $value, int $start_index, int $num): array
{
    return array_fill($start_index, $num, $value);
}
