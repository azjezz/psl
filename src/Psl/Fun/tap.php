<?php

declare(strict_types=1);

namespace Psl\Fun;

/**
 * Runs the given function with the supplied object, then returns the object.
 *
 * Executes the function if a value is provided.
 * Provides a curries version if no value was provided.
 *
 * Example:
 *
 *      $run = Fun\tap(
 *          static function(int $i): void {
 *              echo 'Result: '.$i.PHP_EOL;
 *          }
 *      );
 *
 *      $run(1)
 *      Prints: Result: 1
 *      => Int(1)
 *
 *      $run(2)
 *      Prints: Result: 2
 *      => Int(2)
 *
 *      $run(3)
 *      Prints: Result: 3
 *      => Int(3)
 *
 * More real life example:
 *      Fun\pipe(
 *          Fun\tap(
 *              function (User $user) use ($eventDispatcher): void {
 *                  $eventDispatcher->dispatch(
 *                      new UserCreatedEvent($user->id)
 *                  );
 *              }
 *          )
 *       )($user);
 *
 *       => $user
 *
 * @template T
 *
 * @param (callable(T): void) $callback
 *
 * @return (callable(T): T)
 *
 * @pure
 */
function tap(callable $callback): callable
{
    return static function (mixed $value) use ($callback): mixed {
        $callback($value);

        return $value;
    };
}
