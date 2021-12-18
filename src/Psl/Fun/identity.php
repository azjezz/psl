<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Create a closure that returns the value passed to it as an argument.
 *
 * @template T
 *
 * @return (Closure(T): T)
 *
 * @pure
 */
function identity(): Closure
{
    return static fn ($result) => $result;
}
