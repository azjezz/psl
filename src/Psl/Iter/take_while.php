<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * Takes items from an iterable until the predicate fails for the first time.
 *
 * This means that all elements before (and excluding) the first element on
 * which the predicate fails will be returned.
 *
 * Examples:
 *
 *      Iter\take_while([3, 1, 4, -1, 5], fn($i) => $i > 0)
 *      => Iter(3, 1, 4)
 *
 * @psalm-template  Tk
 * @psalm-template  Tv
 *
 * @psalm-param     iterable<Tk, Tv>        $iterable Iterable to take values from
 * @psalm-param     (callable(Tv): bool)    $predicate
 *
 * @psalm-return    Iterator<Tk, Tv>
 */
function take_while(iterable $iterable, callable $predicate): Iterator
{
    return new Iterator(Gen\take_while($iterable, $predicate));
}
