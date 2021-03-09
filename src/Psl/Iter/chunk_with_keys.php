<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;

/**
 * The same as chunk(), but preserving keys.
 *
 * Examples:
 *
 *     Iter\chunk_with_keys(['a' => 1, 'b' => 2, 'c' => 3], 2)
 *     => Iter(['a' => 1, 'b' => 2], ['c' => 3])
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> $iterable The iterable to chunk
 * @param int $size The size of each chunk
 *
 * @throws Psl\Exception\InvariantViolationException If $size is negative.
 *
 * @return Iterator<int, array<Tk, Tv>>
 *
 * @deprecated since 1.2, use Vec\chunk_with_keys instead.
 */
function chunk_with_keys(iterable $iterable, int $size): Iterator
{
    Psl\invariant($size > 0, 'Expected a non-negative size.');

    return Iterator::from(static function () use ($iterable, $size): Generator {
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
    });
}
