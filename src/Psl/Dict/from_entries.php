<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a dict where each mapping is defined by the given key/value
 * tuples.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<array{0: Tk, 1: Tv}> $entries
 *
 * @psalm-return array<Tk, Tv>
 */
function from_entries(iterable $entries): array
{
    $result = [];
    foreach ($entries as [$key, $value]) {
        $result[$key] = $value;
    }

    return $result;
}
