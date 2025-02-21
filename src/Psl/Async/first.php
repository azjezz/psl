<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Unwraps the first completed awaitable.
 *
 * If you want the first awaitable completed without an error, use {@see any()} instead.
 *
 * @template T
 *
 * @param iterable<Awaitable<T>> $awaitables
 *
 * @throws Exception\InvalidArgumentException If $awaitables is empty.
 *
 * @return T
 *
 * @mago-ignore best-practices/loop-does-not-iterate
 */
function first(iterable $awaitables): mixed
{
    foreach (Awaitable::iterate($awaitables) as $first) {
        foreach ($awaitables as $awaitable) {
            if ($awaitable !== $first) {
                $awaitable->ignore();
            }
        }

        return $first->await();
    }

    throw new Exception\InvalidArgumentException('$awaitables must be a non-empty-iterable.');
}
