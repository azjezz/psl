<?php

declare(strict_types=1);

namespace Psl\Fun;

use Closure;

use function array_reduce;

/**
 * Performs left-to-right function composition.
 *
 * @param Closure(T): T ...$stages
 *
 * @return Closure(T): T
 *
 * @pure
 *
 * @api
 */
function pipe<T>(Closure ...$stages): Closure
{
    return static fn(mixed $input): mixed => array_reduce(
        $stages,
        /**
         * @param (Closure(T): T) $next
         */
        static fn(T $input, Closure $next): T => $next($input),
        $input,
    );
}
