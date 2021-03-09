<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;

/**
 * Returns an iterator formed by merging the iterable elements of the
 * given iterable.
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<iterable<Tk, Tv>> $iterables
 *
 * @return Iterator<Tk, Tv>
 *
 * @deprecated use `Dict\flatten` instead.
 * @see Dict\flatten()
 */
function flatten(iterable $iterables): Iterator
{
    return Iterator::from(static function () use ($iterables): Generator {
        foreach ($iterables as $iterable) {
            foreach ($iterable as $key => $value) {
                yield $key => $value;
            }
        }
    });
}
