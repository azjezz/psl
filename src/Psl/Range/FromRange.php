<?php

declare(strict_types=1);

namespace Psl\Range;

use Generator;
use Psl\Iter;

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
 * @template T of int|float
 *
 * @implements LowerBoundRangeInterface<T>
 *
 * @see RangeInterface::contains()
 * @see LowerBoundRangeInterface::getLowerBound()
 *
 * @immutable
 */
final class FromRange implements LowerBoundRangeInterface
{
    /**
     * @param T $lower_bound
     *
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly int|float $lower_bound,
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
        return $value >= $this->lower_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @param T $lower_bound
     *
     * @return FromRange<T>
     *
     * @psalm-mutation-free
     */
    public function withLowerBound(int|float $lower_bound): FromRange
    {
        return new FromRange(
            $lower_bound,
        );
    }

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return BetweenRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(float|int $upper_bound, bool $upper_inclusive): BetweenRange
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
     * @return FullRange<T>
     *
     * @psalm-mutation-free
     */
    public function withoutLowerBound(): FullRange
    {
        /** @var FullRange<T> */
        return new FullRange();
    }

    /**
     * {@inheritDoc}
     *
     * @param T $upper_bound
     *
     * @return BetweenRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(float|int $upper_bound): BetweenRange
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
     * @param T $upper_bound
     *
     * @return BetweenRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(float|int $upper_bound): BetweenRange
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
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getLowerBound(): int|float
    {
        return $this->lower_bound;
    }

    /**
     * {@inheritDoc}
     *
     * @return Iter\Iterator<int, T>
     *
     * @psalm-mutation-free
     *
     * @psalm-suppress ImpureMethodCall
     * @psalm-suppress InvalidReturnStatement
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidOperand
     * @psalm-suppress MixedOperand
     * @psalm-suppress MixedAssignment
     */
    public function getIterator(): Iter\Iterator
    {
        $bound = $this->lower_bound;

        return Iter\Iterator::from(static function () use ($bound): Generator {
            while (true) {
                yield $bound++;
            }
        });
    }
}
