<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Takes items from an array until the predicate fails for the first time.
 *
 * This means that all elements before (and excluding) the first element on
 * which the predicate fails will be included.
 *
 * Examples:
 *
 *      Iter\take_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Iter(3, 1, 4)
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param    array<Tk, Tv>                $array Array to take values from
 * @psalm-param    (pure-callable(Tv): bool)    $predicate
 *
 * @psalm-return   array<Tk, Tv>
 *
 * @psalm-pure
 */
function take_while(array $array, callable $predicate): array
{
    $result = [];
    foreach ($array as $key => $value) {
        if (!$predicate($value)) {
            return $result;
        }

        $result[$key] = $value;
    }


    return $result;
}
