<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Drops items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements after (and including) the first element on
 * which the predicate fails will be yield.
 *
 * Examples:
 *
 *      Gen\drop_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Gen(-1, 5)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable Iterable to drop values from
 * @psalm-param (callable(Tv): bool) $predicate
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function drop_while(iterable $iterable, callable $predicate): Generator
{
    $failed = false;
    foreach ($iterable as $key => $value) {
        if (!$failed && !$predicate($value)) {
            $failed = true;
        }

        if ($failed) {
            yield $key => $value;
        }
    }
}
