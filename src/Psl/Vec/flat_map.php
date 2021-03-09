<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * @template Tv
 * @template Ts
 *
 * @param iterable<Tv> $iterable Iterable to be mapped over
 * @param (callable(Tv): iterable<Ts>) $mapper
 *
 * @return list<Ts>
 */
function flat_map(iterable $iterable, callable $mapper): array
{
    $flattened = [];
    foreach ($iterable as $value) {
        foreach ($mapper($value) as $item) {
            $flattened[] = $item;
        }
    }

    return $flattened;
}
