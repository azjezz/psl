<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl;
use Psl\Vec;

/**
 * Chunks an iterable into arrays of the specified size.
 *
 * Each chunk is an array (non-lazy), but the chunks are yielded lazily.
 * Keys are not preserved.
 *
 * Examples:
 *
 *      Iter\chunk([1, 2, 3, 4, 5], 2)
 *      => Iter([1, 2], [3, 4], [5])
 *
 * @psalm-template T
 *
 * @psalm-param iterable<T> $iterable The iterable to chunk
 * @psalm-param int         $size     The size of each chunk
 *
 * @psalm-return Iterator<int, list<T>>
 *
 * @throws Psl\Exception\InvariantViolationException If $size is negative.
 *
 * @deprecated since 1.2, use Vec\chunk instead.
 */
function chunk(iterable $iterable, int $size): Iterator
{
    Psl\invariant($size > 0, 'Expected a non-negative size.');

    return Iterator::from(static function () use ($iterable, $size): Generator {
        foreach (Vec\chunk($iterable, $size) as $chunk) {
            yield $chunk;
        }
    });
}
