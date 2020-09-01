<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Get the last key of an iterable, if the iterable is empty, null will be returned.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable
 *
 * @psalm-return   Tk|null
 */
function last_key(iterable $iterable)
{
    $last = null;
    foreach ($iterable as $k => $_) {
        $last = $k;
    }

    return $last;
}
