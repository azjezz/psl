<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Drops items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements after (and including) the first element on
 * which the predicate fails will be included.
 *
 * Examples:
 *
 *      Arr\drop_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Arr(-1, 5)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>             $array Array to drop values from
 * @psalm-param (pure-callable(Tv): bool) $predicate
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function drop_while(array $array, callable $predicate): array
{
    $result = [];
    $failed = false;
    foreach ($array as $key => $value) {
        if (!$failed && !$predicate($value)) {
            $failed = true;
        }

        if ($failed) {
            $result[$key] = $value;
        }
    }

    return $result;
}
