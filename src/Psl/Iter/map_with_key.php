<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Applies a mapping function to all values of an iterator.
 *
 * The function is passed the current iterator key and value and should return a
 * modified iterator value. The key is left as-is.
 *
 * Examples:
 *
 *     Iter\map_with_key([1, 2, 3, 4, 5], fn($k, $v) => $k + $v);
 *     => Iter(1, 3, 5, 7, 9)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 * @psalm-template T
 *
 * @psalm-param iterable<Tk, Tv>        $iterable Iterable to be mapped over
 * @psalm-param (callable(Tk,Tv): T)    $function
 *
 * @psalm-return Iterator<Tk, T>
 *
 * @deprecated use `Dict\map_with_key` instead.
 *
 * @see Dict\map_with_key()
 */
function map_with_key(iterable $iterable, callable $function): Iterator
{
    return Iterator::from(static function () use ($iterable, $function): Generator {
        foreach ($iterable as $key => $value) {
            yield $key => $function($key, $value);
        }
    });
}
