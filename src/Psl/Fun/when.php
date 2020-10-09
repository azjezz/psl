<?php

declare(strict_types=1);

namespace Psl\Fun;

/**
 * Creates a callback that can run a left or right function based on a condition.
 *
 * Example:
 *
 *      $greet = Fun\when(
 *          static fn(string $name): bool => $name === 'Jos',
 *          static fn(string $name): string => 'Bonjour Jos!',
 *          static fn(string $name): string => 'Hello ' . $name . '!'
 *      );
 *
 *      $greet('World')
 *      => Str('Hello World!');
 *
 *      $greet('Jos')
 *      => Str('Bonjour Jos!');
 *
 * @template Ti
 * @template To
 *
 * @psalm-param (callable(Ti): bool)   $condition
 * @psalm-param (callable(Ti): To) $then
 * @psalm-param (callable(Ti): To) $else
 *
 * @psalm-return (callable(Ti): To)
 */
function when(callable $condition, callable $then, callable $else): callable
{
    return static fn ($value) =>  $condition($value) ? $then($value) : $else($value);
}
