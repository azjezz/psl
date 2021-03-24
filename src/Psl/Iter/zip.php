<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Dict;
use Psl\Vec;

/**
 * Zips the iterables that were passed as arguments.
 *
 * Afterwards keys and values will be arrays containing the keys/values of
 * the individual iterables.
 *
 * This function stops as soon as the first iterable becomes invalid.
 *
 *  Examples:
 *
 *     Iter\zip([1, 2, 3], [4, 5, 6], [7, 8, 9, 10])
 *     => Iter(
 *         Arr(0, 0, 0) => Arr(1, 4, 7),
 *         Arr(1, 1, 1) => Arr(2, 5, 8),
 *         Arr(2, 2, 2) => Arr(3, 6, 9)
 *     )
 *
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> ...$iterables
 *
 * @return Iterator<list<Tk>, list<Tv>>
 *
 * @deprecated since 1.2, use Vec\zip instead.
 * @see Vec\zip()
 */
function zip(iterable ...$iterables): Iterator
{
    return Iterator::from(static function () use ($iterables): Generator {
        if (0 === count($iterables)) {
            return;
        }

        $iterators = Vec\values(Dict\map(
            $iterables,
            /**
             * @param iterable<Tk, Tv> $iterable
             *
             * @return Iterator<Tk, Tv>
             */
            static fn ($iterable) => new Iterator((static fn () => yield from $iterable)()),
        ));

        apply($iterators, static fn (Iterator $iterator) => $iterator->rewind());
        while (all($iterators, static fn (Iterator $iterator) => $iterator->valid())) {
            $keys = Vec\values(Dict\map(
                $iterators,
                /**
                 * @param Iterator<Tk, Tv> $iterator
                 *
                 * @return Tk
                 */
                static fn (Iterator $iterator) => $iterator->key(),
            ));

            $values = Vec\values(Dict\map(
                $iterators,
                /**
                 * @param Iterator<Tk, Tv> $iterator
                 *
                 * @return Tv
                 */
                static fn (Iterator $iterator) => $iterator->current(),
            ));

            yield $keys => $values;

            apply($iterators, static fn (Iterator $iterator) => $iterator->next());
        }
    });
}
