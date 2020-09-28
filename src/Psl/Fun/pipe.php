<?php

declare(strict_types=1);

namespace Psl\Fun;

use function Psl\Iter\reduce;

/**
 * Performs left-to-right function composition.
 * Example
 *      $pipe = Fun\pipe(
 *          fn($value) => 'Hello' . $value,
 *          fn($value) => $value . '!',
 *          fn($value) => '¡' . $value
 *      );
 *      => $greet('World') === '¡Hello World!';
 *      => $greet('Jim') === '¡Hello Jim!';
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
         * @param IO $input
         * @param callable(IO): IO $next
         *
         * @return IO
         */
        static fn ($input, int $key, callable $next) => $next($input),
        $input
    );
}
