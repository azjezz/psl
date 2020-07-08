<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl;

/**
 * The same as chunk(), but preserving keys.
 *
 * Examples:
 *
 *     Gen\chunk_with_keys(['a' => 1, 'b' => 2, 'c' => 3], 2)
 *     => Gen(['a' => 1, 'b' => 2], ['c' => 3])
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> $iterable The iterable to chunk
 * @psalm-param int              $size     The size of each chunk
 *
 * @psalm-return Generator<int, array<Tk, Tv>, mixed, void>
 *
 * @throws Psl\Exception\InvariantViolationException If $size is negative.
 */
function chunk_with_keys(iterable $iterable, int $size): Generator
{
    Psl\invariant($size > 0, 'Expected a non-negative size.');

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
