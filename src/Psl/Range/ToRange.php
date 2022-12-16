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
     * @param T $lower_bound
     *
     * @return BetweenRange<T>
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int|float $lower_bound): BetweenRange
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
     * @return FullRange<T>
     *
     * @psalm-mutation-free
     */
    public function withoutUpperBound(): FullRange
    {
        /** @var FullRange<T> */
        return new FullRange();
    }

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return ToRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(float|int $upper_bound, bool $upper_inclusive): ToRange
    {
        return new self($upper_bound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return ToRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(float|int $upper_bound): ToRange
    {
        return new self($upper_bound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return ToRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(float|int $upper_bound): ToRange
    {
        return new self($upper_bound, false);
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

    /**
     * {@inheritDoc}
     *
     * @return static<T>
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
