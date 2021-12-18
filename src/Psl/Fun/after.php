<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that calls the next functions with the result of the first one.
 *
 * @template I
 * @template O
 * @template R
 *
 * @param (Closure(I): O) $first
 * @param (Closure(O): R) $next
 *
 * @return (Closure(I): R)
 *
 * @pure
 */
function after(Closure $first, Closure $next): Closure
{
    return static fn ($input) => $next($first($input));
}
