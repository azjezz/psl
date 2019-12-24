<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Fill an array with values.
 *
 * @psalm-template T
 *
 * @psalm-param T $value
 *
 * @psalm-return array<int, T>
 *
 * @psalm-pure
 */
function fill($value, int $start_index, int $num): array
{
    return \array_fill($start_index, $num, $value);
}
