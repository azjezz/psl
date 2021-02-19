<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl;

/**
 * Returns a vec containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Vec\filter_with_key(['a', '0', 'b', 'c'])
 *      => Vec('b', 'c')
 *
 *      Vec\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn(int $key, string $value): bool => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Vec('baz')
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>                $iterable
 * @psalm-param (callable(Tk, Tv): bool)|null   $predicate
 *
 * @psalm-return list<Tv>
 */
function filter_with_key(iterable $iterable, ?callable $predicate = null): array
{
    $predicate = $predicate ??
        /**
         * @psalm-param Tk $k
         * @psalm-param Tv $v
         */
        static fn ($k, $v): bool => Psl\Internal\boolean($v);

    $result = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k, $v)) {
            $result[] = $v;
        }
    }

    return $result;
}
