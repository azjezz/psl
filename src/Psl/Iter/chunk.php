<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;

/**
 * Chunks an iterable into arrays of the specified size.
 *
 * Each chunk is an array (non-lazy), but the chunks are yielded lazily.
 * Keys are not preserved.
 *
 * Examples:
 *
 *      Iterable\chunk([1, 2, 3, 4, 5], 2)
 *      => Iterable([1, 2], [3, 4], [5])
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T> $iterable The iterable to chunk
 * @psalm-param int         $size     The size of each chunk
 *
 * @psalm-return iterable<array<int, T>> An iterator of arrays
 */
function chunk(iterable $iterable, int $size): iterable
{
    Psl\invariant($size > 0, 'Chunk size must be positive');

    $chunk = [];
    $count = 0;
    foreach ($iterable as $value) {
        $chunk[] = $value;
        ++$count;
        if ($count === $size) {
            yield $chunk;
            $count = 0;
            $chunk = [];
        }
    }
    if (0 !== $count) {
        yield $chunk;
    }
}
