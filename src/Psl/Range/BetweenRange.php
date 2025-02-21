<?php

declare(strict_types=1);

namespace Psl\Range;

use Generator;
use Psl\Iter;

/**
 * A `BetweenRange` is a range that contains all values between the given lower and upper bound.
 *
 * This range can serve as an Iterator, starting from the lower bound, and ending at the upper bound.
 *
 * Example:
 *
 * ```php
 * use Psl\Range;
 *
 * $range = new Range\BetweenRange(1, 10, inclusive: false);
 *
 * foreach ($range as $value) {
 *      // $value will be 1, 2, 3, 4, 5, 6, 7, 8, 9
 * }
 *
 * $range = new Range\BetweenRange(1, 10, inclusive: true);
 *
 * foreach ($range as $value) {
 *     // $value will be 1, 2, 3, 4, 5, 6, 7, 8, 9, 10
 * }
 * ```
 *
 * @see RangeInterface::contains()
 * @see RangeInterface::withLowerBound()
 * @see RangeInterface::withUpperBound()
 * @see LowerBoundRangeInterface::getLowerBound()
 * @see UpperBoundRangeInterface::getUpperBound()
 * @see UpperBoundRangeInterface::withUpperInclusive()
 * @see UpperBoundRangeInterface::isUpperInclusive()
 *
 * @psalm-immutable
 */
final readonly class BetweenRange implements LowerBoundRangeInterface, UpperBoundRangeInterface
{
    private int $lowerBound;
    private int $upperBound;
    private bool $upperInclusive;

    /**
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function __construct(int $lower_bound, int $upper_bound, bool $upper_inclusive = false)
    {
        if ($lower_bound > $upper_bound) {
            throw Exception\InvalidRangeException::lowerBoundIsGreaterThanUpperBound($lower_bound, $upper_bound);
        }

        $this->lowerBound = $lower_bound;
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
        if ($value < $this->lowerBound) {
            return false;
        }

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
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): BetweenRange
    {
        return new BetweenRange($this->lowerBound, $upper_bound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withUpperBoundInclusive(int $upper_bound): BetweenRange
    {
        return new BetweenRange($this->lowerBound, $upper_bound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withUpperBoundExclusive(int $upper_bound): BetweenRange
    {
        return new BetweenRange($this->lowerBound, $upper_bound, false);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withoutLowerBound(): ToRange
    {
        return new ToRange($this->upperBound, $this->upperInclusive);
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
        return new static($lower_bound, $this->upperBound, $this->upperInclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withoutUpperBound(): FromRange
    {
        return new FromRange($this->lowerBound);
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
        /** @psalm-suppress MissingThrowsDocblock */
        return new static($this->lowerBound, $this->upperBound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function getLowerBound(): int
    {
        return $this->lowerBound;
    }

    /**
     * {@inheritDoc}
     *
     * @return Iter\Iterator<int, int>
     *
     * @psalm-mutation-free
     *
     * @psalm-suppress ImpureMethodCall
     */
    #[\Override]
    public function getIterator(): Iter\Iterator
    {
        $lower = $this->lowerBound;
        $upper = $this->upperBound;
        $inclusive = $this->upperInclusive;

        return Iter\Iterator::from(static function () use ($lower, $upper, $inclusive): Generator {
            $to = $inclusive ? $upper : ($upper - 1);

            for ($i = $lower; $i <= $to; $i++) {
                yield $i;
            }
        });
    }
}
