<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Dict;
use Throwable;

/**
 * Awaits all awaitables to complete concurrently.
 *
 * If one or more awaitables fail, all awaitables will be completed before throwing.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Awaitable<Tv>> $awaitables
 *
 * @return array<Tk, Tv> Unwrapped values with the order preserved.
 */
function all(iterable $awaitables): array
{
    $values = [];
    $errors = [];

    // Awaitable::iterate() to throw the first error based on completion order instead of argument order
    foreach (Awaitable::iterate($awaitables) as $index => $awaitable) {
        try {
            $values[$index] = $awaitable->await();
        } catch (Throwable $throwable) {
            $errors[] = $throwable;
        }
    }

    if ($errors !== []) {
        /** @psalm-suppress MissingThrowsDocblock */
        throw $errors[0];
    }

    return Dict\map_with_key(
        $awaitables,
        /**
         * @param Tk $key
         * @param Awaitable<Tv> $_awaitable
         *
         * @retun Tv
         */
        static fn(string|int $key, Awaitable $_awaitable): mixed => $values[$key],
    );
}
