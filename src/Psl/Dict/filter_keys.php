<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns an array containing only the keys for which the given predicate
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
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>           $iterable
 * @psalm-param (callable(Tk): bool)|null  $predicate
 *
 * @psalm-return array<Tk, Tv>
 */
function filter_keys(iterable $iterable, ?callable $predicate = null): array
{
    /** @psalm-var (callable(Tk): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k)) {
            $result[$k] = $v;
        }
    }

    return $result;
}
