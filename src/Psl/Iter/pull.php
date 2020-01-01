<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Returns a new iterable where:
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
 *          0 => 'A', 2 => 'C', 4 => 'D', 8 => 'E', 16 => 'F', 32 => 'G',
 *          64 => 'H', 124 => 'I', 256 => 'J', 512 => 'K', 1024 => 'L'
 *      )
 *
 *
 * k as array-key
 * v
 *
 * @psalm-template T
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<T> $iterable
 * @psalm-param (callable(T): Tv) $value_func
 * @psalm-param (callable(T): Tk) $key_func
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function pull(iterable $iterable, callable $value_func, callable $key_func): Generator
{
    foreach ($iterable as $value) {
        yield $key_func($value) => $value_func($value);
    }
}
