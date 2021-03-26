<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_keys;
use function is_array;

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
    if (is_array($iterable)) {
        return array_keys($iterable);
    }

    $result = [];
    foreach ($iterable as $k => $_v) {
        $result[] = $k;
    }

    return $result;
}
