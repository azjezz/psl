<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 *  Zips the iterables that were passed as arguments.
 *
 * Afterwards keys and values will be arrays containing the keys/values of
 * the individual iterables. This function stops as soon as the first iterable
 * becomes invalid.
 *
 * Examples:
 *
 *      Iter\zip([1, 2, 3], [4, 5, 6], [7, 8, 9, 10])
 *      => Iter(
 *          Arr(0, 0, 0) => Arr(1, 4, 7),
 *          Arr(1, 1, 1) => Arr(2, 5, 8),
 *          Arr(2, 2, 2) => Arr(3, 6, 9)
 *      )
 *
 * @psalm-template Tk as array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv>    ...$iterables
 *
 * @psalm-return iterable<array<int, Tk>, array<int, Tv>>
 */
function zip(iterable ...$iterables): iterable
{
    if (0 === count($iterables)) {
        return;
    }

    /** @psalm-var iterable<Iterator<Tk, Tv>> $iterators */
    $iterators = to_array(map(
        $iterables,
        /**
         * @psalm-param iterable<Tk, Tv>    $iterable
         *
         * @psalm-return Iterator<Tk, Tv>
         */
        static function (iterable $iterable): Iterator {
            return new Iterator($iterable);
        }
    ));

    for (
        apply($iterators, static function (\Iterator $iterator): void {
            $iterator->rewind();
        });
        all($iterators, static function (\Iterator $iterator): bool {
            return $iterator->valid();
        });
        apply($iterators, static function (\Iterator $iterator): void {
            $iterator->next();
        })
    ) {
        /** @psalm-var array<int, Tk> $keys */
        $keys = to_array(map($iterators,
            /**
             * @psalm-param \Iterator<Tk, Tv> $iterator
             *
             * @psalm-return Tk
             */
            static function (\Iterator $iterator) {
                /** @psalm-var Tk */
                return $iterator->key();
            }
        ));

        /** @psalm-var array<int, Tv> $values */
        $values = to_array(map($iterators,
            /**
             * @psalm-param \Iterator<Tk, Tv> $iterator
             * @psalm-return Tv
             */
            static function (\Iterator $iterator) {
                /** @psalm-var Tv */
                return $iterator->current();
            }
        ));

        yield $keys => $values;
    }
}
