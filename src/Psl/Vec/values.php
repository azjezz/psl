<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Return all the values of an array.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return list<T>
 */
function values(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $value;
    }

    return $result;
}
