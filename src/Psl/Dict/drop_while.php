<?php

declare(strict_types=1);

namespace Psl\Dict;

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
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>        $iterable Iterable to drop values from
 * @psalm-param (callable(Tv): bool)    $predicate
 *
 * @psalm-return array<Tk, Tv>
 */
function drop_while(iterable $iterable, callable $predicate): array
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
