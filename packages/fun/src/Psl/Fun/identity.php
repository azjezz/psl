<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Create a closure that returns the value passed to it as an argument.
 *
 * @return (Closure(T): T)
 *
 * @pure
 *
 * @api
 */
function identity<T>(): Closure
{
    return static fn(mixed $result): mixed => $result;
}
