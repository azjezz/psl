<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Returns a dict where each mapping is defined by the given key/value
 * tuples.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<array{0: Tk, 1: Tv}> $entries
 *
 * @return array<Tk, Tv>
 */
function from_entries(iterable $entries): array
{
    $result = [];
    foreach ($entries as [$key, $value]) {
        $result[$key] = $value;
    }

    return $result;
}
