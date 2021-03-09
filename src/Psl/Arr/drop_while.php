<?php

declare(strict_types=1);

namespace Psl\Arr;

use Psl\Dict;

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
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to drop values from
 * @param (callable(Tv): bool) $predicate
 *
 * @return array<Tk, Tv>
 *
 * @deprecated use `Dict\drop_while` instead.
 * @see Dict\drop_while()
 */
function drop_while(iterable $iterable, callable $predicate): array
{
    return Dict\drop_while($iterable, $predicate);
}
