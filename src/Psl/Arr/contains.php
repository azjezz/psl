<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns true if the given iterable contains the value. Strict equality is
 * used.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 * @psalm-param Tk                  $value
 */
function contains(iterable $iterable, $value): bool
{
    foreach ($iterable as $v) {
        if ($value === $v) {
            return true;
        }
    }

    return false;
}
