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
 * @psalm-template Tk of array-key
 * @psalm-template Tv
 *
 * @psalm-param iterable<Tk, Tv|null> $iterable
 *
 * @psalm-return array<Tk, Tv>
 */
function filter_nulls(iterable $iterable): array
{
    /** @var array<Tk, Tv> $result */
    $result = [];
    foreach ($iterable as $key => $value) {
        if (null !== $value) {
            $result[$key] = $value;
        }
    }

    return $result;
}
