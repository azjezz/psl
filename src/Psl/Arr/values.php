<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Return all the values of an array.
 *
 * @psalm-template T
 *
 * @psalm-param array<mixed, T> $arr
 *
 * @psalm-return array<int, T>
 *
 * @psalm-pure
 */
function values(array $arr): array
{
    return \array_values($arr);
}
