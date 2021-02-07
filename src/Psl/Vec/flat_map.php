<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * @psalm-template Tv
 * @psalm-template Ts
 *
 * @psalm-param iterable<Tv>                    $iterable Iterable to be mapped over
 * @psalm-param (callable(Tv): iterable<Ts>)    $mapper
 *
 * @psalm-return list<Ts>
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
