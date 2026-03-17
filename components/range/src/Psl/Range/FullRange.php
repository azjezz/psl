<?php

declare(strict_types=1);

namespace Psl\Range;

use Override;

/**
 * A `FullRange` is a range that contains all values.
 *
 * This range cannot serve as an Iterator because it does not have a starting point.
 *
 * @see RangeInterface::contains()
 *
 * @psalm-immutable
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
    #[Override]
    public function contains(int $value): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withLowerBound(int $lowerBound): FromRange
    {
        return new FromRange($lowerBound);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBound(int $upperBound, bool $upperInclusive): ToRange
    {
        return new ToRange($upperBound, $upperInclusive);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBoundInclusive(int $upperBound): ToRange
    {
        return new ToRange($upperBound, true);
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function withUpperBoundExclusive(int $upperBound): ToRange
    {
        return new ToRange($upperBound, false);
    }
}
