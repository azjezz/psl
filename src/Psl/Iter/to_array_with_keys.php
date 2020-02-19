<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Copy the iterable into an array with keys.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return array<Tk, Tv>
 */
function to_array_with_keys(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$key] = $value;
    }

    return $result;
}
