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
 * @param iterable<Tk, Tv> $iterable The iterable to chunk
 * @param positive-int $size The size of each chunk
 *
 * @return list<array<Tk, Tv>>
 *
 * @api
 */
function chunk_with_keys<Tk : int|string = int|string, Tv = mixed>(iterable $iterable, int $size): array
{
    $result = [];
    $ii = 0;
    $chunkNumber = -1;
    foreach ($iterable as $k => $value) {
        if (($ii % $size) === 0) {
            $chunkNumber++;
            $result[$chunkNumber] = [];
        }

        $result[$chunkNumber][$k] = $value;
        $ii++;
    }

    /** @var list<array<Tk, Tv>> */
    return $result;
}
