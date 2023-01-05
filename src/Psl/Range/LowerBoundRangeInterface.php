<?php

declare(strict_types=1);

namespace Psl\Range;

use IteratorAggregate;
use Psl\Iter;
use Psl\Math;

/**
 * @extends IteratorAggregate<int, int>
 *
 * @immutable
 */
interface LowerBoundRangeInterface extends IteratorAggregate, RangeInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int $upper_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int $upper_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * Remove the lower bound from the range.
     *
     * @psalm-mutation-free
     */
    public function withoutLowerBound(): RangeInterface;

    /**
     * Returns the lower bound of the range.
     *
     * @psalm-mutation-free
     */
    public function getLowerBound(): int;
    
    /**
     * Returns an iterator for the range.
     *
     * If this range has no upper bound, the iterator will be infinite.
     *
     * If {@see Math\INT64_MAX} is reached while iterating, {@see Exception\OverflowException} will be thrown.
     *
     * @return Iter\Iterator<int, int>
     *
     * @psalm-mutation-free
     */
    public function getIterator(): Iter\Iterator;
}
