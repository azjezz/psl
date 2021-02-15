<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
use Psl\Arr;
use Psl\Dict;
use Psl\Vec;

/**
 * Returns the cartesian product of iterables that were passed as arguments.
 *
 * The resulting iterator will contain all the possible tuples of keys and
 * values.
 *
 * Examples:
 *
 *      Iter\product(
 *          Iter\range(1, 2),
 *          Iter\range(3, 4)
 *      )
 *     => Iter([0, 0] => [1, 3], [0, 1] => [1, 4], [1, 0] => [2, 3], [1, 1] => [2, 4])
 *
 * @psalm-template Tk
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv> ...$iterables Iterables to combine
 *
 * @psalm-return Iterator<list<Tk>, list<Tv>>
 *
 * @deprecated ( no replacement is provided )
 */
function product(iterable ...$iterables): Iterator
{
    return Iterator::from(static function () use ($iterables): Generator {
        /** @psalm-var list<Iterator<Tk, Tv>> $iterators */
        $iterators = Vec\values(Dict\map(
            $iterables,
            static fn (iterable $iterable) => Iterator::create($iterable)
        ));

        $numIterators = count($iterators);
        if (0 === $numIterators) {
            /** @psalm-var iterable<list<Tk>, list<Tv>> */
            yield [] => [];

            return;
        }

        /** @psalm-var list<Tk|null> $keyTuple */
        /** @psalm-var list<Tv|null> $valueTuple */
        $keyTuple   = Arr\fill(null, 0, $numIterators);
        $valueTuple = Arr\fill(null, 0, $numIterators);
        $i          = -1;
        while (true) {
            while (++$i < $numIterators - 1) {
                $iterators[$i]->rewind();
                // @codeCoverageIgnoreStart
                if (!$iterators[$i]->valid()) {
                    return;
                }
                // @codeCoverageIgnoreEnd

                $keyTuple[$i]   = $iterators[$i]->key();
                $valueTuple[$i] = $iterators[$i]->current();
            }

            foreach ($iterators[$i] as $keyTuple[$i] => $valueTuple[$i]) {
                yield Vec\values($keyTuple) => Vec\values($valueTuple);
            }

            while (--$i >= 0) {
                $iterators[$i]->next();
                if ($iterators[$i]->valid()) {
                    $keyTuple[$i]   = $iterators[$i]->key();
                    $valueTuple[$i] = $iterators[$i]->current();
                    continue 2;
                }
            }

            return;
        }
    });
}
