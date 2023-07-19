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
 * @immutable
 */
final class BetweenRange implements LowerBoundRangeInterface, UpperBoundRangeInterface
{
    /**
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly int $lower_bound,
        private readonly int $upper_bound,
        private readonly bool $upper_inclusive = false,
    ) {
        if ($this->lower_bound > $this->upper_bound) {
            throw Exception\InvalidRangeException::lowerBoundIsGreaterThanUpperBound(
                $this->lower_bound,
                $this->upper_bound
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function contains(int $value): bool
    {
        if ($value < $this->lower_bound) {
            return false;
        }

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
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): BetweenRange
    {
        return new BetweenRange(
            $this->lower_bound,
            $upper_bound,
            $upper_inclusive,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int $upper_bound): BetweenRange
    {
        return new BetweenRange(
            $this->lower_bound,
            $upper_bound,
            true,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception\InvalidRangeException If the lower bound is greater than the upper bound.
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int $upper_bound): BetweenRange
    {
        return new BetweenRange(
            $this->lower_bound,
            $upper_bound,
            false,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withoutLowerBound(): ToRange
    {
        return new ToRange($this->upper_bound, $this->upper_inclusive);
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
        return new static(
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
    public function withoutUpperBound(): FromRange
    {
        return new FromRange($this->lower_bound);
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
        /** @psalm-suppress MissingThrowsDocblock */
        return new static(
            $this->lower_bound,
            $this->upper_bound,
            $upper_inclusive,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function getLowerBound(): int
    {
        return $this->lower_bound;
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
    public function getIterator(): Iter\Iterator
    {
        $lower = $this->lower_bound;
        $upper = $this->upper_bound;
        $inclusive = $this->upper_inclusive;

        return Iter\Iterator::from(static function () use ($lower, $upper, $inclusive): Generator {
            $to = $inclusive ? $upper : $upper - 1;

            for ($i = $lower; $i <= $to; $i++) {
                yield $i;
            }
        });
    }
}
