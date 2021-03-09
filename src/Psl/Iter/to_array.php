<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Vec;

/**
 * Copy the iterable into an array.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @return list<T>
 *
 * @deprecated since 1.2, use Vec\values instead.
 * @see Vec\values()
 */
function to_array(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $value;
    }

    return $result;
}
