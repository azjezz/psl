<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Convert the given iterable to a dict.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    $iterable
 *
 * @psalm-return array<Tk, Tv>
 */
function from_iterable(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $key => $value) {
        $result[$key] = $value;
    }

    return $result;
}
