<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

/**
 * Takes items from an iterable until the predicate fails for the first time.
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
 * @psalm-param    iterable<Tk, Tv>        $iterable    Iterable to take values from
 * @psalm-param    (callable(Tv): bool)    $predicate
 *
 * @psalm-return   array<Tk, Tv>
 *
 * @deprecated use `Dict\take_while` instead.
 *
 * @see Dict\take_while()
 */
function take_while(iterable $iterable, callable $predicate): array
{
    return Dict\take_while($iterable, $predicate);
}
