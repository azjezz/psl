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
 * @psalm-return list<T>
 * @psalm-pure
 *
 * @return mixed[]
 */
function values(array $arr): array
{
    return \array_values($arr);
}
