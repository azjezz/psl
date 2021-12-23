<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * The same as chunk(), but preserving keys.
 *
 * Examples:
 *
 *     Vec\chunk_with_keys(['a' => 1, 'b' => 2, 'c' => 3], 2)
 *     => Iter(['a' => 1, 'b' => 2], ['c' => 3])
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable The iterable to chunk
 * @param positive-int $size The size of each chunk
 *
 * @return list<array<Tk, Tv>>
 */
function chunk_with_keys(iterable $iterable, int $size): array
{
    $result = [];
    $ii = 0;
    $chunk_number = -1;
    foreach ($iterable as $k => $value) {
        if ($ii % $size === 0) {
            $result[] = [];
            $chunk_number++;
        }

        $result[$chunk_number][$k] = $value;
        $ii++;
    }

    return values($result);
}
