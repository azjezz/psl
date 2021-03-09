<?php

declare(strict_types=1);

namespace Psl\Dict;

use Psl;

/**
 * Returns a dict containing only the keys and values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting both they key and the value to boolean.
 *
 * Example:
 *
 *      Dict\filter_with_key(['a', '0', 'b', 'c'])
 *      => Dict(2 => 'b', 3 => 'c')
 *
 *      Dict\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn(int $key, string $value): bool => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Dict(2 => 'baz')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tk, Tv): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 */
function filter_with_key(iterable $iterable, ?callable $predicate = null): array
{
    $predicate = $predicate ??
        /**
         * @param Tk $k
         * @param Tv $v
         */
        static fn ($k, $v): bool => Psl\Internal\boolean($v);

    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k, $v)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
