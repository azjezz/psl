<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Return all the keys of an array.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable
 *
 * @psalm-return list<Tk>
 */
function keys(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $k => $v) {
        $result[] = $k;
    }

    return $result;
}
