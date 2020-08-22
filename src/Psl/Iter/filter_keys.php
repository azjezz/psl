<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns a new iterable containing only the keys for which the given predicate
 * returns `true`. The default predicate is casting the key to boolean.
 *
 * Example:
 *
 *      Iter\filter_keys([0 => 'a', 1 => 'b'])
 *      => Iter(1 => 'b')
 *
 *      Iter\filter_keys([0 => 'a', 1 => 'b', 2 => 'c'], fn($key) => $key <= 1);
 *      => Iter(0 => 'a', 1 => 'b')
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>            $iterable
 * @psalm-param    null|(callable(Tk): bool)   $predicate
 *
 * @psalm-return   Iterator<Tk, Tv>
 *
 * @see            Gen\filter_keys()
 */
function filter_keys(iterable $iterable, ?callable $predicate = null): Iterator
{
    return new Iterator(Gen\filter_keys($iterable, $predicate));
}
