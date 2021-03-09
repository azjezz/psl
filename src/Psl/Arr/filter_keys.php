<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns a dict containing only the keys for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the key to boolean.
 *
 * Example:
 *
 *      Arr\filter_keys([0 => 'a', 1 => 'b'])
 *      => Arr(1 => 'b')
 *
 *      Arr\filter_keys([0 => 'a', 1 => 'b', 2 => 'c'], fn(int $key): bool => $key <= 1);
 *      => Arr(0 => 'a', 1 => 'b')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tk): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\filter_keys` instead.
 * @see Dict\filter_keys()
 */
function filter_keys(iterable $iterable, ?callable $predicate = null): array
{
    return Dict\filter_keys($iterable, $predicate);
}
