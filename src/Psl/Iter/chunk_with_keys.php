<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl;

/**
 * The same as chunk(), but preserving keys.
 *
 * Examples:
 *
 *     Iterable\chunk_with_keys(['a' => 1, 'b' => 2, 'c' => 3], 2)
 *     => Iterable(['a' => 1, 'b' => 2], ['c' => 3])
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable The iterable to chunk
 * @psalm-param int              $size     The size of each chunk
 *
 * @psalm-return iterable<array<Tk, Tv>> An iterator of arrays
 */
function chunk_with_keys(iterable $iterable, int $size): iterable
{
    Psl\invariant($size > 0, 'Chunk size must be positive');

    $chunk = [];
    $count = 0;
    foreach ($iterable as $key => $value) {
        $chunk[$key] = $value;
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
