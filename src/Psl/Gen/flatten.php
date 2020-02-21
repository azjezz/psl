<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Returns a generator formed by merging the iterable elements of the
 * given iterable.
 *
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<iterable<Tk, Tv>> $iterables
 *
 * @psalm-return Generator<Tk, Tv, mixed, void>
 */
function flatten(iterable $iterables): Generator
{
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    }
}
