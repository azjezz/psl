<?php

declare(strict_types=1);

namespace Psl\Fun;

/**
 * Returns a function that calls the next function with the result of the first function.
 *
 * Example:
 *
 *      $runExpression = Fun\after(
 *          fn($i) => $i +  1,
 *          fn(int $i) => $i * 5
 *      );
 *      => $runExpression(1) === 10;
 *      => $runExpression(2) === 15;
 *      => $runExpression(3) === 20;
 *
 *      $greet = Fun\after(
 *          fn($value) => 'Hello' . $value,
 *          fn($value) => $value . '!'
 *      );
 *      => $greet('World') === 'Hello World!';
 *      => $greet('Jim') === 'Hello Jim!';
 *
 * @template I
 * @template O
 * @template R
 *
 * @psalm-param callable(I): O $first
 * @psalm-param callable(O): R $next
 *
 * @psalm-return callable(I): R
 *
 * @psalm-pure
 */
function after(callable $first, callable $next): callable
{
    return fn ($input) => $next($first($input));
}
