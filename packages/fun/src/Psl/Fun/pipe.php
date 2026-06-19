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
function pipe<T = mixed>(Closure ...$stages): Closure
{
    return static fn(mixed $input): mixed => array_reduce(
        $stages,
        /**
         * @param T $input
         * @param (Closure(T): T) $next
         *
         * @return T
         */
        static fn(mixed $input, Closure $next): mixed => $next($input),
        $input,
    );
}
