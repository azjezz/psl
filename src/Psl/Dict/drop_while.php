<?php

declare(strict_types=1);

namespace Psl\Dict;

use Closure;

/**
 * Drops items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements after (and including) the first element on
 * which the predicate fails will be included.
 *
 * Examples:
 *
 *      Dict\drop_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Dict(-1, 5)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to drop values from
 * @param (Closure(Tv): bool) $predicate
 *
 * @return array<Tk, Tv>
 */
function drop_while(iterable $iterable, Closure $predicate): array
{
    $result = [];
    $failed = false;
    foreach ($iterable as $key => $value) {
        if (!$failed && !$predicate($value)) {
            $failed = true;
        }

        if ($failed) {
            $result[$key] = $value;
        }
    }

    return $result;
}
