<?php

declare(strict_types=1);

namespace Psl\Iter;

/**
 * Check whether an iterable is empty.
 *
 * @psalm-assert-if-true empty $iterable
 */
function is_empty(iterable $iterable): bool
{
    return 0 === count($iterable);
}
