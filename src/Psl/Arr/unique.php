<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array in which each value appears exactly once.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param array<Tk, Tv>   $array
 *
 * @psalm-return array<Tk, Tv>
 *
 * @psalm-pure
 */
function unique(array $array): array
{
    /** @psalm-var array<Tk, Tv> $unique */
    $unique = [];

    foreach ($array as $k => $value) {
        if (!contains($unique, $value)) {
            $unique[$k] = $value;
        }
    }

    return $unique;
}
