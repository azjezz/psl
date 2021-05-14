<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if the given iterable contains the key.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 * @param Tk $key
 */
function contains_key(iterable $iterable, mixed $key): bool
{
    foreach ($iterable as $k => $_v) {
        if ($key === $k) {
            return true;
        }
    }

    return false;
}
