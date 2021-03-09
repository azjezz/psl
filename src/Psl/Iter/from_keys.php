<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Returns an iterator where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk> $keys
 * @param (callable(Tk): Tv) $value_func
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\from_keys` instead.
 * @see Dict\from_keys()
 */
function from_keys(iterable $keys, callable $value_func): Iterator
{
    return Iterator::from(static function () use ($keys, $value_func): Generator {
        foreach ($keys as $key) {
            yield $key => $value_func($key);
        }
    });
}
