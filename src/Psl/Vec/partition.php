<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * Returns a pair containing lists for which the given predicate returned
 * `true` and `false`, respectively.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 * @param (Closure(T): bool) $predicate
 *
 * @return array{0: list<T>, 1: list<T>}
 */
function partition(iterable $iterable, Closure $predicate): array
{
    $success = [];
    $failure = [];
    foreach ($iterable as $value) {
        if ($predicate($value)) {
            $success[] = $value;
        } else {
            $failure[] = $value;
        }
    }

    return [$success, $failure];
}
