<?php

declare(strict_types=1);

namespace Psl\Range;

use IteratorAggregate;
use Override;
use Psl\Iter;

/**
 * @extends IteratorAggregate<int, int>
 *
 * @psalm-immutable
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
    #[Override]
    public function withUpperBound(
        int $upperBound,
        bool $upperInclusive,
    ): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBoundInclusive(int $upperBound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBoundExclusive(int $upperBound): UpperBoundRangeInterface&LowerBoundRangeInterface;

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
     * If `PHP_INT_MAX` is reached while iterating, {@see Exception\OverflowException} will be thrown.
     *
     * @return Iter\Iterator<int, int>
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getIterator(): Iter\Iterator;
}
