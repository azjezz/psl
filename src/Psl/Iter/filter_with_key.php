<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns an iterator containing only the keys and values for which the given predicate
 * returns `true`. The default predicate is casting both they key and the value to boolean.
 *
 * Example:
 *
 *      Iter\filter(['a', '0', 'b', 'c'])
 *      => Iter('b', 'c')
 *
 *      Iter\filter(['foo', 'bar', 'baz', 'qux'], fn($key, $value) => $key > 1 && Str\contains($value, 'a'));
 *      => Iter('baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>                 $iterable
 * @psalm-param    null|(callable(Tk, Tv): bool)    $predicate
 *
 * @psalm-return   Iterator<Tk, Tv>
 *
 * @see            Gen\filter_with_key()
 */
function filter_with_key(iterable $iterable, ?callable $predicate = null): Iterator
{
    return new Iterator(Gen\filter_with_key($iterable, $predicate));
}
