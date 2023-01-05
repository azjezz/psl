<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @immutable
 */
interface UpperBoundRangeInterface extends RangeInterface
{
    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int $lower_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * Remove the upper bound from the range.
     *
     * @psalm-mutation-free
     */
    public function withoutUpperBound(): RangeInterface;
    
    /**
     * Returns the upper bound of the range.
     *
     * @psalm-mutation-free
     */
    public function getUpperBound(): int;

    /**
     * Returns whether the upper bound is inclusive.
     *
     * @psalm-mutation-free
     */
    public function isUpperInclusive(): bool;

    /**
     * Return a new instance, where the upper bound inclusive flag is set to the given value.
     *
     * @psalm-mutation-free
     */
    public function withUpperInclusive(bool $upper_inclusive): static;
}
