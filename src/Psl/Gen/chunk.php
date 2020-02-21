<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;

/**
 * Chunks an iterable into arrays of the specified size.
 *
 * Each chunk is an array (non-lazy), but the chunks are yielded lazily.
 * Keys are not preserved.
 *
 * Examples:
 *
 *      Gen\chunk([1, 2, 3, 4, 5], 2)
 *      => Gen([1, 2], [3, 4], [5])
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T> $iterable The iterable to chunk
 * @psalm-param int         $size     The size of each chunk
 *
 * @psalm-return Generator<int, list<T>, mixed, void>
 */
function chunk(iterable $iterable, int $size): Generator
{
    Psl\invariant($size > 0, 'Chunk size must be positive');

    /** @var list<T> $chunk */
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
