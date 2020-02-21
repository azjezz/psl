<?php

declare(strict_types=1);

namespace Psl\Arr;

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
 */
function values(array $arr): array
{
    return \array_values($arr);
}
