<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param iterable<Tk, Tv>            $iterable Iterable to be mapped over
 * @psalm-param (callable(Tv): iterable<T>) $mapper
 *
 * @psalm-return list<T>
 *
 * @deprecated since 1.2, use Vec\flat_map instead.
 */
function flat_map(iterable $iterable, callable $mapper): array
{
    return Vec\flat_map($iterable, $mapper);
}
