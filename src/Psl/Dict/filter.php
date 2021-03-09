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
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tv): bool)|null $predicate
 *
 * @return array<Tk, Tv>
 */
function filter(iterable $iterable, ?callable $predicate = null): array
{
    /** @var (callable(Tv): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($v)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
