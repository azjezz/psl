<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * a range is a set of values that are contained in the range.
 *
 * @template T of int|float
 *
 * @immutable
 */
interface RangeInterface
{
    /**
     * Checks if the given value is contained in the range.
     *
     * @param T $value
     *
     * @psalm-mutation-free
     */
    public function contains(int|float $value): bool;

    /**
     * Combine this range with the given lower bound.
     *
     * @param T $lower_bound
     *
     * @return LowerBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int|float $lower_bound): LowerBoundRangeInterface;

    /**
     * Combine this range with the given upper bound.
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(int|float $upper_bound, bool $upper_inclusive): UpperBoundRangeInterface;

    /**
     * Combine this range with the given upper bound, and make it inclusive.
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int|float $upper_bound): UpperBoundRangeInterface;

    /**
     * Combine this range with the given upper bound, and make it exclusive.
     *
     * @param T $upper_bound
     *
     * @return UpperBoundRangeInterface<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int|float $upper_bound): UpperBoundRangeInterface;
}
