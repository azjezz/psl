<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Drops items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements after (and including) the first element on
 * which the predicate fails will be returned.
 *
 * Examples:
 *
 *      Iter\drop_while([3, 1, 4, -1, 5], fm($i) => $i > 0)
 *      => Iter(-1, 5)
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv>     $iterable Iterable to drop values from
 * @psalm-param    (callable(Tv): bool) $predicate
 *
 * @psalm-return   Iterator<Tk, Tv>
 *
 * @see            Gen\drop_while()
 */
function drop_while(iterable $iterable, callable $predicate): Iterator
{
    return new Iterator(Gen\drop_while($iterable, $predicate));
}
