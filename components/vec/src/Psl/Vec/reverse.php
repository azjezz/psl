<?php

declare(strict_types=1);

namespace Psl\Vec;

use function array_reverse;
use function array_values;
use function is_array;

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
    if (is_array($iterable)) {
        return array_reverse(array_values($iterable));
    }

    $values = namespace\values($iterable);

    return array_reverse($values);
}
