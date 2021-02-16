<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Converts an iterable of key and value pairs, into a vec of entries.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable
 *
 * @return list<array{0: Tk, 1: Tv}>
 */
function enumerate(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $k => $v) {
        $result[] = [$k, $v];
    }

    return $result;
}
