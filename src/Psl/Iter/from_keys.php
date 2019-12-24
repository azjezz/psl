<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns a new dict where each value is the result of calling the given
 * function on the corresponding key.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk>        $keys
 * @psalm-param (callable(Tk): Tv)  $value_func
 *
 * @psalm-return iterable<Tk, Tv>
 */
function from_keys(iterable $keys, callable $value_func): iterable
{
    foreach ($keys as $key) {
        yield $key => $value_func($key);
    }
}
