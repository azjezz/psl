<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return Iterator<Tk, Tv>
 */
function rewindable(iterable $iterable): Iterator
{
    return new Iterator($iterable);
}
