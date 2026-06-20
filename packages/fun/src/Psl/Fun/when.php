<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that returns the result of the `$then` function if the condition is true,
 * otherwise the result of the `$else` function.
 *
 * @param (Closure(Ti): bool) $condition
 * @param (Closure(Ti): To) $then
 * @param (Closure(Ti): To) $else
 *
 * @return (Closure(Ti): To)
 *
 * @pure
 *
 * @api
 */
function when<Ti, To>(Closure $condition, Closure $then, Closure $else): Closure
{
    return static fn(mixed $value): mixed => $condition($value) ? $then($value) : $else($value);
}
