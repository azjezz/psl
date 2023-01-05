<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * a range is a set of values that are contained in the range.
 *
 * @immutable
 */
interface RangeInterface
{
    /**
     * Checks if the given value is contained in the range.
     *
     * @psalm-mutation-free
     */
    public function contains(int $value): bool;

    /**
     * Combine this range with the given lower bound.
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int $lower_bound): LowerBoundRangeInterface;

    /**
     * Combine this range with the given upper bound.
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): UpperBoundRangeInterface;

    /**
     * Combine this range with the given upper bound, and make it inclusive.
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int $upper_bound): UpperBoundRangeInterface;

    /**
     * Combine this range with the given upper bound, and make it exclusive.
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int $upper_bound): UpperBoundRangeInterface;
}
