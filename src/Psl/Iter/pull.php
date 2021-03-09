<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Returns an iterator where:
 *  - values are the result of calling `$value_func` on the original value
 *  - keys are the result of calling `$key_func` on the original value.
 *
 * Example:
 *
 *      Iter\pull(
 *          Iter\range(0, 10),
 *          fn($i) => Str\chr($i + 65),
 *          fn($i) => 2**$i,
 *      )
 *      => Iter(
 *          1 => 'A', 2 => 'B', 4 => 'C', 8 => 'D', 16 => 'E', 32 => 'F',
 *          64 => 'G', 128 => 'H', 256 => 'I', 512 => 'J', 1024 => 'K'
 *      )
 *
 * @template T
 * @template Tk
 * @template Tv
 *
 * @param iterable<T> $iterable
 * @param (callable(T): Tv) $value_func
 * @param (callable(T): Tk) $key_func
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\pull` instead.
 * @see Dict\pull()
 */
function pull(iterable $iterable, callable $value_func, callable $key_func): Iterator
{
    return Iterator::from(static function () use ($iterable, $value_func, $key_func): Generator {
        foreach ($iterable as $value) {
            yield $key_func($value) => $value_func($value);
        }
    });
}
