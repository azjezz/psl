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
 * @psalm-immutable
 */
final readonly class FromRange implements LowerBoundRangeInterface
{
    private int $lowerBound;

    /**
     * @psalm-mutation-free
     */
    public function __construct(int $lower_bound)
    {
        $this->lowerBound = $lower_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function contains(int $value): bool
    {
        return $value >= $this->lowerBound;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[\Override]
    public function withLowerBound(int $lower_bound): FromRange
    {
        return new FromRange($lower_bound);
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
    public function withoutLowerBound(): FullRange
    {
        return new FullRange();
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
        $bound = $this->lowerBound;

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
