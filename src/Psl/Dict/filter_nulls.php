<?php

declare(strict_types=1);

namespace Psl\Dict;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Dict\filter_nulls([1, null, 5])
 *      => Dict(0 => 1, 2 => 5)
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Tv|null> $iterable
 *
 * @return array<Tk, Tv>
 */
function filter_nulls(iterable $iterable): array
{
    /** @var array<Tk, Tv> */
    return filter(
        $iterable,
        /**
         * @param Tv|null $value
         */
        static fn($value): bool => $value !== null
    );
}
