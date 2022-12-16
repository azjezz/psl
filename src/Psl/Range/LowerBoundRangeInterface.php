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
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(int|float $upper_bound, bool $upper_inclusive): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int|float $upper_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int|float $upper_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * Remove the lower bound from the range.
     *
     * @return RangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withoutLowerBound(): RangeInterface;

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
