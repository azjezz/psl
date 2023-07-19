<?php

declare(strict_types=1);

namespace Psl\Range;

use Generator;
use Psl\Iter;
use Psl\Math;

/**
 * A `FromRange` is a range that contains all values greater than or equal to the given lower bound.
 *
 * This range can serve as an Iterator, starting from the lower bound.
 *
 * ```php
 * use Psl\Range;
 *
 * $range = new Range\FromRange(1);
 *
 * foreach ($range as $value) {
 *    // $value will be 1, 2, 3, 4, 5, ...
 * }
 * ```
 *
 * Iterating over this range is not recommended, as it is an infinite range.
 *
 * @see RangeInterface::contains()
 * @see LowerBoundRangeInterface::getLowerBound()
 *
 * @immutable
 */
final class FromRange implements LowerBoundRangeInterface
{
    /**
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly int $lower_bound,
    ) {
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function contains(int $value): bool
    {
        return $value >= $this->lower_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int $lower_bound): FromRange
    {
        return new FromRange(
            $lower_bound,
        );
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
    public function withoutLowerBound(): FullRange
    {
        return new FullRange();
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
        $bound = $this->lower_bound;

        return Iter\Iterator::from(static function () use ($bound): Generator {
            $value = $bound;
            while (true) {
                yield $value;

                if ($value === Math\INT64_MAX) {
                    throw Exception\OverflowException::whileIterating($bound);
                }

                $value += 1;
            }
        });
    }
}
