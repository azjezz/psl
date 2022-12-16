<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @template T of int|float
 *
 * @extends RangeInterface<T>
 *
 * @immutable
 */
interface UpperBoundRangeInterface extends RangeInterface
{
    /**
     * {@inheritDoc}
     *
     * @param T $lower_bound
     *
     * @return UpperBoundRangeInterface<T>&LowerBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int|float $lower_bound): UpperBoundRangeInterface&LowerBoundRangeInterface;

    /**
     * Remove the upper bound from the range.
     *
     * @return RangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withoutUpperBound(): RangeInterface;
    
    /**
     * Returns the upper bound of the range.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getUpperBound(): int|float;

    /**
     * Returns whether the upper bound is inclusive.
     *
     * @psalm-mutation-free
     */
    public function isUpperInclusive(): bool;

    /**
     * Return a new instance, where the upper bound inclusive flag is set to the given value.
     *
     * @return static<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperInclusive(bool $upper_inclusive): static;
}
