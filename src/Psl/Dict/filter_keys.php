<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a dict containing only the keys for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the key to boolean.
 *
 * Example:
 *
 *      Dict\filter_keys([0 => 'a', 1 => 'b'])
 *      => Dict(1 => 'b')
 *
 *      Dict\filter_keys([0 => 'a', 1 => 'b', 2 => 'c'], fn(int $key): bool => $key <= 1);
 *      => Dict(0 => 'a', 1 => 'b')
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv>           $iterable
 * @param (callable(Tk): bool)|null  $predicate
 *
 * @return array<Tk, Tv>
 */
function filter_keys(iterable $iterable, ?callable $predicate = null): array
{
    /** @var (callable(Tk): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
