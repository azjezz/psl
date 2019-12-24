<?php

declare(strict_types=1);

namespace Psl\Iter;

use Psl\Arr;

/**
 * Determines whether a value is an iterable.
 *
 * Only arrays and objects implementing Traversable are considered as iterable.
 * In particular objects that don't implement Traversable are not considered as
 * iterable, even though PHP would accept them in a foreach() loop.
 *
 * Examples:
 *
 *     Iter\is_iterable([1, 2, 3])
 *     => Bool(true)
 *
 *     Iter\is_iterable(new ArrayIterator([1, 2, 3]))
 *     => Bool(true)
 *
 *     Iter\is_iterable(new stdClass())
 *     => Bool(false)
 *
 * @psalm-param mixed $value
 *
 * @psalm-assert-if-true iterable<array-key,mixed> $value
 */
function is_iterable($value): bool
{
    return Arr\is_array($value) || $value instanceof \Traversable;
}
