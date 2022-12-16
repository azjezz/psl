<?php

declare(strict_types=1);

namespace Psl\Range;

use IteratorAggregate;
use Psl\Iter;

/**
 * @template T of int|float
 *
 * @extends RangeInterface<T>
 * @extends IteratorAggregate<int, T>
 *
 * @immutable
 */
interface LowerBoundRangeInterface extends IteratorAggregate, RangeInterface
{
    /**
     * Returns the lower bound of the range.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getLowerBound(): int|float;

    /**
     * Returns an iterator for the range.
     *
     * If this range has no upper bound, the iterator will be infinite.
     *
     * Iterating over an infinite range is considered an undefined behavior.
     *
     * @return Iter\Iterator<int, T>
     *
     * @psalm-mutation-free
     */
    public function getIterator(): Iter\Iterator;
}
