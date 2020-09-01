<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Internal;
use Generator;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Iter\filter_nulls([1, null, 5])
 *      => Iter(1, 5)
 *
 * @psalm-template T
 *
 * @psalm-param iterable<null|T> $iterable
 *
 * @psalm-return Iterator<int, T>
 */
function filter_nulls(iterable $iterable): Iterator
{
    return Internal\lazy_iterator(static function() use($iterable): Generator {
        foreach($iterable as $value) {
            if ($value !== null) {
                yield $value;
            }
        }
    });
}
