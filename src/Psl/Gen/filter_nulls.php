<?php

declare(strict_types=1);

namespace Psl\Gen;

use Generator;

/**
 * Filter out null values from the given iterable.
 *
 * Example:
 *      Gen\filter_nulls([1, null, 5])
 *      => Gen(1, 5)
 *
 * @psalm-template T
 *
 * @psalm-param iterable<null|T> $iterable
 *
 * @psalm-return Generator<int, T, mixed, void>
 */
function filter_nulls(iterable $iterable): Generator
{
    foreach ($iterable as $value) {
        if (null !== $value) {
            yield $value;
        }
    }
}
