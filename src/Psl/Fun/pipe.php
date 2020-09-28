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
 * @psalm-param list<callable(mixed): mixed> $stages
 *
 * @psalm-return callable(mixed): mixed
 *
 * @psalm-pure
 */
function pipe(callable ...$stages): callable
{
    return fn ($input) => reduce(
        $stages,
        /**
         * @template IO of mixed
         *
         * @param IO $input
         * @param callable(IO): IO $next
         *
         * @return IO
         */
        fn ($input, int $key, callable $next) => $next($input),
        $input
    );
}
