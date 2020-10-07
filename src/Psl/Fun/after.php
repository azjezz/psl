<?php

declare(strict_types=1);

namespace Psl\Fun;

/**
 * Returns a function that calls the next function with the result of the first function.
 *
 * Example:
 *
 *      $run = Fun\after(
 *          static fn(int $i): int => $i + 1,
 *          static fn(int $i): int => $i * 5,
 *      );
 *
 *      $run(1)
 *      => Int(10)
 *
 *      $run(2)
 *      => Int(15)
 *
 *      $run(3)
 *      => Int(20)
 *
 * @template I
 * @template O
 * @template R
 *
 * @psalm-param (callable(I): O) $first
 * @psalm-param (callable(O): R) $next
 *
 * @psalm-return (callable(I): R)
 *
 * @psalm-pure
 */
function after(callable $first, callable $next): callable
{
    return static fn ($input) => $next($first($input));
}
