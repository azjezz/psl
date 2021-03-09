<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;
use Generator;
use Psl\Dict;

/**
 * Returns a generator containing only the values for which the given predicate
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
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tv): bool)|null $predicate
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\filter` instead.
 * @see Dict\filter()
 */
function filter(iterable $iterable, ?callable $predicate = null): Iterator
{
    return Iterator::from(static function () use ($iterable, $predicate): Generator {
        /** @var (callable(Tv): bool) $predicate */
        $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
        foreach ($iterable as $k => $v) {
            if ($predicate($v)) {
                yield $k => $v;
            }
        }
    });
}
