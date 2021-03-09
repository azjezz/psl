<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns an array containing only the keys and values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting both they key and the value to boolean.
 *
 * Example:
 *
 *      Arr\filter_with_key(['a', '0', 'b', 'c'])
 *      => Iter('b', 'c')
 *
 *      Arr\filter_with_key(
 *          ['foo', 'bar', 'baz', 'qux'],
 *          fn(int $key, string $value): bool => $key > 1 && Str\contains($value, 'a')
 *      );
 *      => Arr('baz')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tk, Tv): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\filter_with_key` instead.
 * @see Dict\filter_with_key()
 */
function filter_with_key(iterable $iterable, ?callable $predicate = null): array
{
    return Dict\filter_with_key($iterable, $predicate);
}
