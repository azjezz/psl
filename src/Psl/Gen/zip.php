<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;
use Psl\Iter;

/**
 * Zips the iterables that were passed as arguments.
 *
 *  Afterwards keys and values will be arrays containing the keys/values of
 *  the individual iterables. This function stops as soon as the first iterable
 *  becomes invalid.
 *
 *  Examples:
 *
 *     Gen\zip([1, 2, 3], [4, 5, 6], [7, 8, 9, 10])
 *     => Gen(
 *         Arr(0, 0, 0) => Arr(1, 4, 7),
 *         Arr(1, 1, 1) => Arr(2, 5, 8),
 *         Arr(2, 2, 2) => Arr(3, 6, 9)
 *     )
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> ...$iterables
 *
 * @psalm-return Generator<list<Tk>, list<Tv>, mixed, void>
 */
function zip(iterable ...$iterables): Generator
{
    if (0 === count($iterables)) {
        return;
    }

    /** @psalm-var list<Iter\Iterator<Tk, Tv>> $iterators */
    $iterators = Iter\to_array(map(
        $iterables,
        /**
         * @psalm-param  iterable<Tk, Tv>    $iterable
         *
         * @psalm-return Iter\Iterator<Tk, Tv>
         */
        fn ($iterable) => new Iter\Iterator($iterable),
    ));

    for (
        Iter\apply($iterators, fn (Iter\Iterator $iterator) => $iterator->rewind());
        Iter\all($iterators, fn (Iter\Iterator $iterator) => $iterator->valid());
        Iter\apply($iterators, fn (Iter\Iterator $iterator) => $iterator->next())
    ) {
        /** @psalm-var list<Tk> $keys */
        $keys = Iter\to_array(map(
            $iterators,
            /**
             * @psalm-param Iter\Iterator<Tk, Tv> $iterator
             *
             * @psalm-return Tk
             */
            fn (Iter\Iterator $iterator) => $iterator->key(),
        ));

        /** @psalm-var list<Tv> $values */
        $values = Iter\to_array(map(
            $iterators,
            /**
             * @psalm-param Iter\Iterator<Tk, Tv> $iterator
             *
             * @psalm-return Tv
             */
            fn (Iter\Iterator $iterator) => $iterator->current(),
        ));

        yield $keys => $values;
    }
}
