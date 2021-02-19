<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the value to boolean.
 *
 * Example:
 *
 *      Dict\filter(['', '0', 'a', 'b'])
 *      => Dict(2 => 'a', 3 => 'b')
 *
 *      Dict\filter(['foo', 'bar', 'baz', 'qux'], fn(string $value): bool => Str\contains($value, 'a'));
 *      => Dict(1 => 'bar', 2 => 'baz')
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>            $iterable
 * @psalm-param (callable(Tv): bool)|null   $predicate
 *
 * @psalm-return array<Tk, Tv>
 */
function filter(iterable $iterable, ?callable $predicate = null): array
{
    /** @psalm-var (callable(Tv): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($v)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
