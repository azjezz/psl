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
 * @psalm-immutable
 */
final readonly class ToRange implements UpperBoundRangeInterface
{
    private int $upperBound;
    private bool $upperInclusive;

    /**
     * @psalm-mutation-free
     */
    public function __construct(int $upper_bound, bool $upper_inclusive = false)
    {
        $this->upperBound = $upper_bound;
        $this->upperInclusive = $upper_inclusive;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function contains(int $value): bool
    {
        if ($this->upperInclusive) {
            return $value <= $this->upperBound;
        }

        return $value < $this->upperBound;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withLowerBound(int $lower_bound): BetweenRange
    {
        return new BetweenRange($lower_bound, $this->upperBound, $this->upperInclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withoutUpperBound(): FullRange
    {
        return new FullRange();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): ToRange
    {
        return new self($upper_bound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withUpperBoundInclusive(int $upper_bound): ToRange
    {
        return new self($upper_bound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withUpperBoundExclusive(int $upper_bound): ToRange
    {
        return new self($upper_bound, false);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function getUpperBound(): int
    {
        return $this->upperBound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function isUpperInclusive(): bool
    {
        return $this->upperInclusive;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withUpperInclusive(bool $upper_inclusive): static
    {
        return new static($this->upperBound, $upper_inclusive);
    }
}
