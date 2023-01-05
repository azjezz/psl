<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * A `ToRange` is a range that contains all values up to the upper bound.
 *
 * This range cannot serve as an Iterator because it does not have a starting point.
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
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly int $upper_bound,
        private readonly bool $upper_inclusive = false,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function contains(int $value): bool
    {
        if ($this->upper_inclusive) {
            return $value <= $this->upper_bound;
        }

        return $value < $this->upper_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int $lower_bound): BetweenRange
    {
        return new BetweenRange(
            $lower_bound,
            $this->upper_bound,
            $this->upper_inclusive,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withoutUpperBound(): FullRange
    {
        return new FullRange();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): ToRange
    {
        return new self($upper_bound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int $upper_bound): ToRange
    {
        return new self($upper_bound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int $upper_bound): ToRange
    {
        return new self($upper_bound, false);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function getUpperBound(): int
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

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withUpperInclusive(bool $upper_inclusive): static
    {
        return new static(
            $this->upper_bound,
            $upper_inclusive,
        );
    }
}
