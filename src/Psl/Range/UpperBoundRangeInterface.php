<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * @template T of int|float
 *
 * @extends RangeInterface<T>
 *
 * @immutable
 */
interface UpperBoundRangeInterface extends RangeInterface
{
    /**
     * Returns the upper bound of the range.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getUpperBound(): int|float;

    /**
     * Returns whether the upper bound is inclusive.
     *
     * @psalm-mutation-free
     */
    public function isUpperInclusive(): bool;
}
