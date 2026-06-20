<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Returns a 2-tuple containing dict for which the given predicate returned
 * `true` and `false`, respectively.
 *
 * @param iterable<Tk, Tv> $iterable
 * @param (Closure(Tk, Tv): bool) $predicate
 *
 * @return array{0: array<Tk, Tv>, 1: array<Tk, Tv>}
 *
 * @api
 */
function partition_with_key<Tk: string|int, Tv>(iterable $iterable, Closure $predicate): array
{
    $success = [];
    $failure = [];
    foreach ($iterable as $key => $value) {
        if ($predicate($key, $value)) {
            $success[$key] = $value;

            continue;
        }

        $failure[$key] = $value;
    }

    return [$success, $failure];
}
