<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Returns a new iterable containing only the values for which the given predicate
 * returns `true`. The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Iter\filter(['', '0', 'a', 'b'])
 *      => Iter('a', 'b')
 *
 *      Iter\filter(['foo', 'bar', 'baz', 'qux'], fn($value) => Str\contains($value, 'a'));
 *      => Iter('bar', 'baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>            $iterable
 * @psalm-param    null|(callable(Tv): bool)   $predicate
 *
 * @psalm-return   Iterator<Tk, Tv>
 *
 * @see            Gen\filter()
 */
function filter(iterable $iterable, ?callable $predicate = null): Iterator
{
    return new Iterator(Gen\filter($iterable, $predicate));
}
