<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;
use Psl\Dict;

/**
 * Returns an iterator containing only the keys and values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting both they key and the value to boolean.
 *
 * Example:
 *
 *      Iter\filter_with_key(['a', '0', 'b', 'c'])
 *      => Iter('b', 'c')
 *
 *      Iter\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn($key, $value) => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Iter('baz')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tk, Tv): bool)|null $predicate
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\filter_with_key` instead.
 * @see Dict\filter_with_key()
 */
function filter_with_key(iterable $iterable, ?callable $predicate = null): Iterator
{
    return Iterator::from(static function () use ($iterable, $predicate): Generator {
        $predicate = $predicate ??
            /**
             * @param Tk $_k
             * @param Tv $v
             *
             * @return bool
             */
            static fn ($_k, $v) => Psl\Internal\boolean($v);

        foreach ($iterable as $k => $v) {
            if ($predicate($k, $v)) {
                yield $k => $v;
            }
        }
    });
}
