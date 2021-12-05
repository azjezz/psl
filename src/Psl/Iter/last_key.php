<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns the last key of an iterable, if the iterable is empty, null will be returned.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return Tk|null
 */
function last_key(iterable $iterable)
{
    $last = null;
    foreach ($iterable as $k => $_) {
        $last = $k;
    }

    return $last;
}
