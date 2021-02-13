<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Return all the values of an array.
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<T> $iterable
 *
 * @psalm-return   list<T>
 */
function values(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $value;
    }

    return $result;
}
