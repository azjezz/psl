<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_values;
use function is_array;

/**
 * Return all the values of an array.
 *
 * @param iterable<T> $iterable
 *
 * @return list<T>
 *
 * @api
 */
function values<T = mixed>(iterable $iterable): array
{
    if (is_array($iterable)) {
        return array_values($iterable);
    }

    $result = [];
    foreach ($iterable as $value) {
        $result[] = $value;
    }

    return $result;
}
