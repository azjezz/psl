<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Vec;

/**
 * Reverse the given iterable.
 *
 * Example:
 *      Iter\reverse(['foo', 'bar', 'baz', 'qux'])
 *      => Iter('qux', 'baz', 'bar', 'foo')
 *
 * @template T
 *
 * @param iterable<T> $iterable The iterable to reverse.
 *
 * @return Iterator<int, T>
 *
 * @deprecated since 1.2, use Vec\reverse instead.
 * @see Vec\reverse()
 */
function reverse(iterable $iterable): Iterator
{
    return Iterator::from(static function () use ($iterable): Generator {
        $values = Vec\values($iterable);
        $size   = namespace\count($values);

        if (0 === $size) {
            return;
        }

        for ($i = $size - 1; $i >= 0; $i--) {
            yield $values[$i];
        }
    });
}
