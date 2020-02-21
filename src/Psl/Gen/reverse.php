<?php

declare(strict_types=1);

namespace Psl\Gen;

use Psl\Iter;
use Generator;

/**
 * Reverse the given iterable.
 *
 * Example:
 *      Gen\reverse(['foo', 'bar', 'baz', 'qux'])
 *      => Gen('qux', 'baz', 'bar', 'foo')
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<T> $iterable The iterable to reverse.
 *
 * @psalm-return   Generator<int, T, mixed, void>
 */
function reverse(iterable $iterable): Generator
{
    $size = Iter\count($iterable);
    if (0 === $size) {
        return;
    }

    $values = Iter\to_array($iterable);
    for ($lo = 0, $hi = $size - 1; $lo < $hi; $lo++, $hi--) {
        yield $values[$hi];
    }

    for (; $lo >= 0; --$lo) {
        yield $values[$lo];
    }
}
