<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * Returns a vec containing only the values for which the given predicate
 * returns `true`.
 *
 * The default predicate is casting the key to boolean.
 *
 * Example:
 *
 *      Vec\filter_keys([0 => 'a', 1 => 'b'])
 *      => Vec('b')
 *
 *      Vec\filter_keys([0 => 'a', 1 => 'b', 2 => 'c'], fn(int $key): bool => $key <= 1);
 *      => Vec('a', 'b')
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (callable(Tk): bool)|null $predicate
 *
 * @return list<Tv>
 */
function filter_keys(iterable $iterable, ?callable $predicate = null): array
{
    /** @var (callable(Tk): bool) $predicate */
    $predicate = $predicate ?? Closure::fromCallable('Psl\Internal\boolean');
    $result    = [];
    foreach ($iterable as $k => $v) {
        if ($predicate($k)) {
            $result[] = $v;
        }
    }

    return $result;
}
