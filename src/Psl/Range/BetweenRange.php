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
 * @template T of int|float
 *
 * @implements LowerBoundRangeInterface<T>
 * @implements UpperBoundRangeInterface<T>
 *
 * @see RangeInterface::contains()
 * @see LowerBoundRangeInterface::getLowerBound()
 * @see UpperBoundRangeInterface::getUpperBound()
 * @see UpperBoundRangeInterface::isUpperInclusive()
 *
 * @immutable
 */
final class BetweenRange implements LowerBoundRangeInterface, UpperBoundRangeInterface
{
    /**
     * @param T $lower_bound
     * @param T $upper_bound
     *
     * @psalm-mutation-free
     */
    public function __construct(
        private readonly int|float $lower_bound,
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
        $lower = $this->lower_bound;
        $upper = $this->upper_bound;
        $inclusive = $this->upper_inclusive;

        return Iter\Iterator::from(static function () use ($lower, $upper, $inclusive): Generator {
            $to = $inclusive ? $upper : $upper - 1;
            
            for ($i = $lower; $i <= $to; $i++) {
                /** @var T $i */
                yield $i;
            }
        });
    }
}
