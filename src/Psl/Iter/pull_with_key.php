<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Returns an iterator where:
 *  - values are the result of calling `$value_func` on the original value/key
 *  - keys are the result of calling `$key_func` on the original value/key.
 *
 * Example:
 *
 *      Iter\pull_with_key(
 *          Iter\range(0, 10),
 *          fn($k, $v) => Str\chr($v + $k + 65),
 *          fn($k, $v) => 2**($v+$k)
 *      )
 *      => Iter(
 *          1 => 'A', 4 => 'C', 16 => 'E', 64 => 'G', 256 => 'I', 1024 => 'K',
 *          4096 => 'M', 16384 => 'O', 65536 => 'Q', 262144 => 'S', 1048576 => 'U'
 *      )
 *
 * @template Tk1
 * @template Tv1
 * @template Tk2
 * @template Tv2
 *
 * @param iterable<Tk1, Tv1> $iterable
 * @param (callable(Tk1, Tv1): Tv2) $value_func
 * @param (callable(Tk1, Tv1): Tk2) $key_func
 *
 * @return Iterator<Tk2, Tv2>
 *
 * @deprecated use `Dict\pull_with_key` instead.
 * @see Dict\pull_with_key()
 */
function pull_with_key(iterable $iterable, callable $value_func, callable $key_func): Iterator
{
    return Iterator::from(static function () use ($iterable, $value_func, $key_func): Generator {
        foreach ($iterable as $key => $value) {
            yield $key_func($key, $value) => $value_func($key, $value);
        }
    });
}
