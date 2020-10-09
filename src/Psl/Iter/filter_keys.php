<?php

declare(strict_types=1);

namespace Psl\Iter;

use Closure;
use Generator;

/**
 * Returns an iterator containing only the keys for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the key to boolean.
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
 * @psalm-param iterable<Tk, Tv>            $iterable
 * @psalm-param (callable(Tk): bool)|null   $predicate
 *
 * @psalm-return Iterator<Tk, Tv>
 */
function filter_keys(iterable $iterable, ?callable $predicate = null): Iterator
{
    return Iterator::from(static function () use ($iterable, $predicate): Generator {
        /** @psalm-var (callable(Tk): bool) $predicate */
        $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
        foreach ($iterable as $k => $v) {
            if ($predicate($k)) {
                yield $k => $v;
            }
        }
    });
}
