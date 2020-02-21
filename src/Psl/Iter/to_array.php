<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Copy the iterable into an array.
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<T> $iterable
 *
 * @psalm-return   list<T>
 */
function to_array(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $value;
    }

    return $result;
}
