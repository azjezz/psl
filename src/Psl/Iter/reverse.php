<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Arr;

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
    return Iterator::from(static function () use ($iterable): Generator {
        $values = to_array($iterable);
        $size   = Arr\count($values);
        if (0 === $size) {
            return;
        }

        for ($i = $size - 1; $i >= 0; $i--) {
            yield $values[$i];
        }
    });
}
