<?php

declare(strict_types=1);

namespace Psl\Arr;

/**
 * Returns a new array in which each value appears exactly once.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 *
 * @psalm-return array<Tk, Tv>
 */
function unique(iterable $iterable): array
{
    /** @psalm-var array<Tk, Tv> $unique */
    $unique = [];

    foreach ($iterable as $k => $value) {
        if (!contains($unique, $value)) {
            $unique[$k] = $value;
        }
    }

    return $unique;
}
