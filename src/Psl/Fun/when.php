<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

/**
 * Returns a closure that returns the result of the `$then` function if the condition is true,
 * otherwise the result of the `$else` function.
 *
 * @template Ti
 * @template To
 *
 * @param (Closure(Ti): bool) $condition
 * @param (Closure(Ti): To) $then
 * @param (Closure(Ti): To) $else
 *
 * @return (Closure(Ti): To)
 *
 * @pure
 */
function when(Closure $condition, Closure $then, Closure $else): Closure
{
    return static fn ($value) =>  $condition($value) ? $then($value) : $else($value);
}
