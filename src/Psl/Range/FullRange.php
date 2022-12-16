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
}
