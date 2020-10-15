<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if the given iterable contains the key.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable,
 * @psalm-param    Tk               $key
 *
 * @psalm-return   bool
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
