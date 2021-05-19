<?php

declare(strict_types=1);

namespace Psl\Vec;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Vec\filter_nulls([1, null, 5])
 *      => Vec(1, 5)
 *
 * @template T
 *
 * @param iterable<T|null> $iterable
 *
 * @return list<T>
 */
function filter_nulls(iterable $iterable): array
{
    /** @var list<T> */
    return filter(
        $iterable,
        /**
         * @param T|null $value
         */
        static fn($value): bool => null !== $value
    );
}
