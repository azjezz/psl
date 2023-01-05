<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * A `FullRange` is a range that contains all values.
 *
 * This range cannot serve as an Iterator because it does not have a starting point.
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
     * @return true
     *
     * @psalm-mutation-free
     */
    public function contains(int $value): bool
    {
        return true;
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
     * @psalm-mutation-free
     */
    public function withUpperBound(int $upper_bound, bool $upper_inclusive): ToRange
    {
        return new ToRange($upper_bound, $upper_inclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundInclusive(int $upper_bound): ToRange
    {
        return new ToRange($upper_bound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    public function withUpperBoundExclusive(int $upper_bound): ToRange
    {
        return new ToRange($upper_bound, false);
    }
}
