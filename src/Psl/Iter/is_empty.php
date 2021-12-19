<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Returns true if the given iterable is empty.
 *
 * @template T
 *
 * @param iterable<T> $iterable
 *
 * @psalm-assert-if-true empty $iterable
 */
function is_empty(iterable $iterable): bool
{
    return 0 === namespace\count($iterable);
}
