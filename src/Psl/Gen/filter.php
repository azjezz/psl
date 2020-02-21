<?php

declare(strict_types=1);

namespace Psl\Gen;

use Closure;
use Generator;

/**
 * Returns a generator containing only the values for which the given predicate
 * returns `true`. The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Gen\filter(['', '0', 'a', 'b'])
 *      => Gen('a', 'b')
 *
 *      Gen\filter(['foo', 'bar', 'baz', 'qux'], fn($value) => Str\contains($value, 'a'));
 *      => Gen('bar', 'baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>            $iterable
 * @psalm-param null|(callable(Tv): bool)   $predicate
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function filter(iterable $iterable, ?callable $predicate = null): Generator
{
    /** @psalm-var (callable(Tv): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    foreach ($iterable as $k => $v) {
        if ($predicate($v)) {
            yield $k => $v;
        }
    }
}
