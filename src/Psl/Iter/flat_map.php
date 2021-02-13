<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Vec;

/**
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param iterable<Tk, Tv>            $iterable Iterable to be mapped over
 * @psalm-param (callable(Tv): iterable<T>) $mapper
 *
 * @psalm-return iterable<int, T>
 *
 * @deprecated since 1.2, use Vec\flat_map instead.
 *
 * @see Vec\flat_map()
 */
function flat_map(iterable $iterable, callable $mapper): iterable
{
    return Iterator::from(static function () use ($iterable, $mapper) {
        foreach ($iterable as $value) {
            foreach ($mapper($value) as $item) {
                yield $item;
            }
        }
    });
}
