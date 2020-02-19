<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Copy the iterable into an array.
 *
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tv> $iterable
 *
 * @psalm-return list<Tv>
 */
function to_array(iterable $iterable): array
{
    $result = [];
    foreach ($iterable as $value) {
        $result[] = $value;
    }

    return $result;
}
