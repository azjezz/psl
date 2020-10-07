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
 * @psalm-param callable(T): T ...$stages
 *
 * @psalm-return callable(T): T
 *
 * @psalm-pure
 */
function pipe(callable ...$stages): callable
{
    return static fn ($input) => reduce(
        $stages,
        /**
         * @template IO
         *
         * @psalm-param IO                  $input
         * @psalm-param (callable(IO): IO)  $next
         *
         * @psalm-return IO
         */
        static fn ($input, int $key, callable $next) => $next($input),
        $input
    );
}
