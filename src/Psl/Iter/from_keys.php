<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;

/**
 * Returns an iterator where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk>        $keys
 * @psalm-param (callable(Tk): Tv)  $value_func
 *
 * @psalm-return Iterator<Tk, Tv>
 */
function from_keys(iterable $keys, callable $value_func): Iterator
{
    return Iterator::from(static function () use ($keys, $value_func): Generator {
        foreach ($keys as $key) {
            yield $key => $value_func($key);
        }
    });
}
