<?php

declare(strict_types=1);

namespace Psl\Range;

/**
 * a range is a set of values that are contained in the range.
 *
 * @template T of int|float
 *
 * @immutable
 */
interface RangeInterface
{
    /**
     * Checks if the given value is contained in the range.
     *
     * @param T $value
     *
     * @psalm-mutation-free
     */
    public function contains(int|float $value): bool;
}
