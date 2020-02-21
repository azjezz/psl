<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Gen;

/**
 * The same as chunk(), but preserving keys.
 *
 * Examples:
 *
 *     Iter\chunk_with_keys(['a' => 1, 'b' => 2, 'c' => 3], 2)
 *     => Iter(['a' => 1, 'b' => 2], ['c' => 3])
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param    iterable<Tk, Tv> $iterable The iterable to chunk
 * @psalm-param    int              $size     The size of each chunk
 *
 * @psalm-return   Iterator<int, array<Tk, Tv>>
 *
 * @see            Gen\chunk_with_keys()
 */
function chunk_with_keys(iterable $iterable, int $size): Iterator
{
    return new Iterator(Gen\chunk_with_keys($iterable, $size));
}
