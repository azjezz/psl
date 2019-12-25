<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;

/**
 * Returns a new iterable containing only the keys and values for which the given predicate
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
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>            $iterable
 * @psalm-param    null|(callable(Tk, Tv): bool)   $predicate
 *
 * @psalm-return   iterable<Tk, Tv>
 */
function filter_with_key(iterable $iterable, ?callable $predicate = null): iterable
{
    $predicate = $predicate ??
        /**
         * @psalm-param Tk $k
         * @psalm-param Tv $v
         *
         * @return bool
         */
        fn ($k, $v) => Psl\Internal\boolean($v);

    foreach ($iterable as $k => $v) {
        if ($predicate($k, $v)) {
            yield $k => $v;
        }
    }
}
