<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Returns an array containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Arr\filter(['', '0', 'a', 'b'])
 *      => Arr('a', 'b')
 *
 *      Arr\filter(['foo', 'bar', 'baz', 'qux'], fn(string $value): bool => Str\contains($value, 'a'));
 *      => Arr('bar', 'baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>            $iterable
 * @psalm-param (callable(Tv): bool)|null   $predicate
 *
 * @psalm-return array<Tk, Tv>
 *
 * @deprecated use `Dict\filter` instead.
 *
 * @see Dict\filter()
 */
function filter(iterable $iterable, ?callable $predicate = null): array
{
    return Dict\filter($iterable, $predicate);
}
