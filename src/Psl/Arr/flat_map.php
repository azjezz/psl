<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param iterable<Tk, Tv>            $iterable Iterable to be mapped over
 * @psalm-param (callable(Tv): iterable<T>) $mapper
 *
 * @psalm-return list<T>
 */
function flat_map(iterable $iterable, callable $mapper): array
{
    $flattened = [];
    foreach ($iterable as $value) {
        foreach ($mapper($value) as $item) {
            $flattened[] = $item;
        }
    }

    return $flattened;
}
