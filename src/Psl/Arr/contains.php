<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns true if the given array contains the value. Strict equality is
 * used.
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>   $array
 * @psalm-param Tk              $value
 *
 * @psalm-pure
 */
function contains(array $array, $value): bool
{
    foreach ($array as $v) {
        if ($value === $v) {
            return true;
        }
    }

    return false;
}
