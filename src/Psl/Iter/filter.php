<?php

declare(strict_types=1);

namespace Psl\Iter;

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
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>            $iterable
 * @psalm-param null|(callable(Tv): bool)   $predicate
 *
 * @psalm-return iterable<Tk, Tv>
 */
function filter(iterable $iterable, ?callable $predicate = null): iterable
{
    $predicate = $predicate ?? \Closure::fromCallable('Psl\Internal\boolean');
    foreach ($iterable as $k => $v) {
        if ($predicate($v)) {
            yield $k => $v;
        }
    }
}
