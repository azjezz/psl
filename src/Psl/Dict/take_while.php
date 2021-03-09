<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Takes items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements before (and excluding) the first element on
 * which the predicate fails will be included.
 *
 * Examples:
 *
 *      Dict\take_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Dict(3, 1, 4)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to take values from
 * @param (callable(Tv): bool) $predicate
 *
 * @return array<Tk, Tv>
 */
function take_while(iterable $iterable, callable $predicate): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        if (!$predicate($value)) {
            return $result;
        }

        $result[$key] = $value;
    }


    return $result;
}
