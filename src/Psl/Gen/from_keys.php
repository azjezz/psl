<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Returns a generator where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk>        $keys
 * @psalm-param (callable(Tk): Tv)  $value_func
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function from_keys(iterable $keys, callable $value_func): Generator
{
    foreach ($keys as $key) {
        yield $key => $value_func($key);
    }
}
