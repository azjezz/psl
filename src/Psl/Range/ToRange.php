<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * A `ToRange` is a range that contains all values up to the upper bound.
 *
 * This range cannot serve as an Iterator because it doesn’t have a starting point.
 *
 * @template T of int|float
 *
 * @implements UpperBoundRangeInterface<T>
 *
 * @see RangeInterface::contains()
 * @see UpperBoundRangeInterface::getUpperBound()
 * @see UpperBoundRangeInterface::isUpperInclusive()
 *
 * @immutable
 */
final class ToRange implements UpperBoundRangeInterface
{
    /**
     * @param T $upper_bound
     *
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly int|float $upper_bound,
        private readonly bool $upper_inclusive = false,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @param T $value
     *
     * @psalm-mutation-free
     */
    public function contains(int|float $value): bool
    {
        if ($this->upper_inclusive) {
            return $value <= $this->upper_bound;
        }

        return $value < $this->upper_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getUpperBound(): int|float
    {
        return $this->upper_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function isUpperInclusive(): bool
    {
        return $this->upper_inclusive;
    }
}
