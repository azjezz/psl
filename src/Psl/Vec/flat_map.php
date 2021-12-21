<?php

declare(strict_types=1);

namespace Psl\Vec;

use Closure;

/**
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tv> $iterable Iterable to be mapped over
 * @param (Closure(Tv): iterable<Ts>) $mapper
 *
 * @return list<Ts>
 */
function flat_map(iterable $iterable, Closure $mapper): array
{
    $flattened = [];
    foreach ($iterable as $value) {
        foreach ($mapper($value) as $item) {
            $flattened[] = $item;
        }
    }

    return $flattened;
}
