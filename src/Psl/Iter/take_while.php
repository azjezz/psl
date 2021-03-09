<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Takes items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements before (and excluding) the first element on
 * which the predicate fails will be yield.
 *
 * Examples:
 *
 *      Iter\take_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Iter(3, 1, 4)
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable Iterable to take values from
 * @param (callable(Tv): bool) $predicate
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\take_while` instead.
 * @see Dict\take_while()
 */
function take_while(iterable $iterable, callable $predicate): Iterator
{
    return Iterator::from(static function () use ($iterable, $predicate): Generator {
        foreach ($iterable as $key => $value) {
            if (!$predicate($value)) {
                return;
            }

            yield $key => $value;
        }
    });
}
