<?php

declare(strict_types=1);

namespace Psl\Iter;

use Generator;
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
 * @template Tk
 * @template Tv
 *
 * @param iterable<Tk, Tv> ...$iterables Iterables to combine
 *
 * @return Iterator<list<Tk>, list<Tv>>
 *
 * @deprecated ( no replacement is provided )
 */
function product(iterable ...$iterables): Iterator
{
    return Iterator::from(static function () use ($iterables): Generator {
        /** @var list<Iterator<Tk, Tv>> $iterators */
        $iterators = Vec\values(Dict\map(
            $iterables,
            static fn (iterable $iterable) => Iterator::create($iterable)
        ));

        $numIterators = count($iterators);
        if (0 === $numIterators) {
            /** @var iterable<list<Tk>, list<Tv>> */
            yield [] => [];

            return;
        }

        /** @var list<Tk|null> $keyTuple */
        /** @var list<Tv|null> $valueTuple */
        $keyTuple   = Vec\fill($numIterators, null);
        $valueTuple = Vec\fill($numIterators, null);
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
