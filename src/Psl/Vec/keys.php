<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Return all the keys of an array.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return list<Tk>
 */
function keys(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $k => $_v) {
        $result[] = $k;
    }

    return $result;
}
