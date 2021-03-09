<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Get the last value of an iterable, if the iterable is empty, returns null.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Tv|null
 */
function last(iterable $iterable)
{
    $last = null;
    foreach ($iterable as $v) {
        $last = $v;
    }

    return $last;
}
