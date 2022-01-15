<?php

declare(strict_types=1);

namespace Psl\Async;

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
 * @throws Exception\CompositeException If all $awaitables errored.
 * @throws Exception\InvalidArgumentException If $awaitables is empty.
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
        } catch (Throwable $exception) {
            $errors[] = $exception;
        }
    }

    if ([] === $errors) {
        throw new Exception\InvalidArgumentException('$awaitables must be a non-empty-iterable.');
    }

    throw new CompositeException($errors);
}
