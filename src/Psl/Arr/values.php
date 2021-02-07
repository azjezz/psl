<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

use function array_values;

/**
 * Return all the values of an array.
 *
 * @psalm-template T
 *
 * @psalm-param    array<array-key, T> $arr
 *
 * @psalm-return   list<T>
 *
 * @psalm-pure
 *
 * @deprecated since 1.2, use Vec\values instead.
 *
 * @see Vec\values()
 */
function values(array $arr): array
{
    return array_values($arr);
}
