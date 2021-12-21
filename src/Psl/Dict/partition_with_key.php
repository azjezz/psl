<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a 2-tuple containing dict for which the given predicate returned
 * `true` and `false`, respectively.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk, Tv): bool) $predicate
 *
 * @return array{0: array<Tk, Tv>, 1: array<Tk, Tv>}
 */
function partition_with_key(iterable $iterable, Closure $predicate): array
{
    $success = [];
    $failure = [];
    foreach ($iterable as $key => $value) {
        if ($predicate($key, $value)) {
            $success[$key] = $value;
        } else {
            $failure[$key] = $value;
        }
    }

    return [$success, $failure];
}
