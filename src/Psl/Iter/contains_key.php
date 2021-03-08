<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if the given iterable contains the key.
 *
 * @template Tk
 * @template Tv
 *
 * @param    iterable<Tk, Tv> $iterable,
 * @param    Tk               $key
 *
 * @return   bool
 */
function contains_key(iterable $iterable, $key): bool
{
    foreach ($iterable as $k => $v) {
        if ($key === $k) {
            return true;
        }
    }

    return false;
}
