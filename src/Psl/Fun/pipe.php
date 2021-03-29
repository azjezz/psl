<?php

declare(strict_types=1);

namespace Psl\Fun;

use function Psl\Iter\reduce;

/**
 * Performs left-to-right function composition.
 *
 * Example:
 *
 *      $greet = Fun\pipe(
 *          static fn(string $value): string => 'Hello' . $value,
 *          static fn(string $value): string => $value . '!',
 *          static fn(string $value): string => '¡' . $value
 *      );
 *
 *      $greet('World')
 *      => Str('¡Hello World!');
 *
 *      $greet('Jim')
 *      => Str('¡Hello Jim!');
 *
 * @template T
 *
 * @param callable(T): T ...$stages
 *
 * @return callable(T): T
 *
 * @pure
 */
function pipe(callable ...$stages): callable
{
    return static fn ($input) => reduce(
        $stages,
        /**
         * @param T $input
         * @param (callable(T): T) $next
         *
         * @return T
         */
        static fn ($input, callable $next) => $next($input),
        $input
    );
}
