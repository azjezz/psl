<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Unwraps the first completed awaitable.
 *
 * If you want the first awaitable completed without an error, use {@see any()} instead.
 *
 * @param iterable<Awaitable<T>> $awaitables
 *
 * @throws Exception\InvalidArgumentException If $awaitables is empty.
 *
 * @api
 */
function first<T>(iterable $awaitables): T
{
    foreach (Awaitable::iterate::<int, T>($awaitables) as $first) {
        foreach ($awaitables as $awaitable) {
            if ($awaitable === $first) {
                continue;
            }

            $awaitable->ignore();
        }

        return $first->await();
    }

    throw new Exception\InvalidArgumentException('$awaitables must be a non-empty-iterable.');
}
