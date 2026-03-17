<?php

declare(strict_types=1);

namespace Psl\Range;

use Override;

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
    public function __construct(int $upperBound, bool $upperInclusive = false)
    {
        $this->upperBound = $upperBound;
        $this->upperInclusive = $upperInclusive;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
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
    #[Override]
    public function withLowerBound(int $lowerBound): BetweenRange
    {
        return new BetweenRange($lowerBound, $this->upperBound, $this->upperInclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withoutUpperBound(): FullRange
    {
        return new FullRange();
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBound(int $upperBound, bool $upperInclusive): ToRange
    {
        return new self($upperBound, $upperInclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBoundInclusive(int $upperBound): ToRange
    {
        return new self($upperBound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBoundExclusive(int $upperBound): ToRange
    {
        return new self($upperBound, false);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getUpperBound(): int
    {
        return $this->upperBound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isUpperInclusive(): bool
    {
        return $this->upperInclusive;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperInclusive(bool $upperInclusive): static
    {
        return new static($this->upperBound, $upperInclusive);
    }
}
