<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that calls the next functions with the result of the first one.
 *
 * @param (Closure(I): O) $first
 * @param (Closure(O): R) $next
 *
 * @return (Closure(I): R)
 *
 * @pure
 *
 * @api
 */
function after<I = mixed, O = mixed, R = mixed>(Closure $first, Closure $next): Closure
{
    return static fn(mixed $input): mixed => $next($first($input));
}
