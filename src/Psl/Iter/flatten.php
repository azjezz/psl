<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns a new dict formed by merging the iterable elements of the
 * given iterable.
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<iterable<Tk, Tv>> $iterables
 *
 * @psalm-return iterable<Tk, Tv>
 */
function flatten(iterable $iterables): iterable
{
    foreach ($iterables as $iterable) {
        foreach ($iterable as $key => $value) {
            yield $key => $value;
        }
    }
}
