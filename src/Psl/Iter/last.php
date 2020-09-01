<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Get the last value of an iterable, if the iterable is empty, returns null.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable
 *
 * @psalm-return   Tv|null
 */
function last(iterable $iterable)
{
    $last = null;
    foreach ($iterable as $v) {
        $last = $v;
    }

    return $last;
}
