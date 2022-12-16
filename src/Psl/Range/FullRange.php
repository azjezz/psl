<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * A `FullRange` is a range that contains all values.
 *
 * This range cannot serve as an Iterator because it does not have a starting point.
 *
 * @template T of int|float
 *
 * @implements RangeInterface<T>
 *
 * @see RangeInterface::contains()
 *
 * @immutable
 */
final class FullRange implements RangeInterface
{
    /**
     * This function always returns true.
     *
     * @param T $value
     *
     * @return true
     *
     * @psalm-mutation-free
     */
    public function contains(int|float $value): bool
    {
        return true;
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
     * @return ToRange<T>
     *
     * @psalm-mutation-free
     */
    public function withUpperBound(float|int $upper_bound, bool $upper_inclusive): ToRange
    {
        return new ToRange($upper_bound, $upper_inclusive);
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
        return new ToRange($upper_bound, true);
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
        return new ToRange($upper_bound, false);
    }
}
