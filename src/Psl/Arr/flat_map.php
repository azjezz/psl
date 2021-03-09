<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Vec;

/**
 * @template Tk of array-key
 * @template Tv
 * @template T
 *
 * @param iterable<Tk, Tv> $iterable Iterable to be mapped over
 * @param (callable(Tv): iterable<T>) $mapper
 *
 * @return list<T>
 *
 * @deprecated use `Vec\flat_map` instead.
 * @see Vec\flat_map()
 */
function flat_map(iterable $iterable, callable $mapper): array
{
    return Vec\flat_map($iterable, $mapper);
}
