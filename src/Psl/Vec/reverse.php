<?php

declare(strict_types=1);

namespace Psl\Vec;

use Psl\Iter;

/**
 * Reverse the given iterable.
 *
 * Example:
 *      Vec\reverse(['foo', 'bar', 'baz', 'qux'])
 *      => Vec('qux', 'baz', 'bar', 'foo')
 *
 * @template T
 *
 * @param iterable<T> $iterable The iterable to reverse.
 *
 * @return list<T>
 */
function reverse(iterable $iterable): array
{
    $values = namespace\values($iterable);
    if ([] === $values) {
        return [];
    }

    $size   = Iter\count($values);
    $result = [];
    for ($i = $size - 1; $i >= 0; $i--) {
        $result[] = $values[$i];
    }

    return $result;
}
