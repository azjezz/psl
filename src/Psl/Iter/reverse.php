<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Internal;

/**
 * Reverse the given iterable.
 *
 * Example:
 *      Iter\reverse(['foo', 'bar', 'baz', 'qux'])
 *      => Iter('qux', 'baz', 'bar', 'foo')
 *
 * @psalm-template T
 *
 * @psalm-param    iterable<T> $iterable The iterable to reverse.
 *
 * @psalm-return   Iterator<int, T>
 */
function reverse(iterable $iterable): Iterator
{
    return Internal\lazy_iterator(static function () use ($iterable): Generator {
        $size = count($iterable);
        if (0 === $size) {
            return;
        }

        $values = to_array($iterable);
        for ($lo = 0, $hi = $size - 1; $lo < $hi; $lo++, $hi--) {
            yield $values[$hi];
        }

        for (; $lo >= 0; --$lo) {
            yield $values[$lo];
        }
    });
}
