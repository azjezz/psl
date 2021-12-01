<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl;
use Psl\Async\Exception\CompositeException;
use Throwable;

/**
 * Unwraps the first successfully completed awaitable.
 *
 * If you want the first awaitable completed, successful or not, use {@see first()} instead.
 *
 * @template T
 *
 * @param iterable<Awaitable<T>> $awaitables
 *
 * @throws CompositeException If all $awaitables errored.
 * @throws Psl\Exception\InvariantViolationException If no $awaitables were provided.
 *
 * @return T
 */
function any(iterable $awaitables): mixed
{
    $errors = [];
    foreach (Awaitable::iterate($awaitables) as $first) {
        try {
            $result = $first->await();
            foreach ($awaitables as $awaitable) {
                if ($awaitable !== $first) {
                    $awaitable->ignore();
                }
            }

            return $result;
        } catch (Throwable $throwable) {
            $errors[] = $throwable;
        }
    }

    Psl\invariant([] !== $errors, 'No awaitables were provided.');

    throw new CompositeException($errors);
}
