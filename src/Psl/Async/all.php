<?php

declare(strict_types=1);

namespace Psl\Async;

/**
 * Awaits all awaitables to complete or aborts if any errors concurrently.
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

    // Awaitable::iterate() to throw the first error based on completion order instead of argument order
    foreach (Awaitable::iterate($awaitables) as $index => $awaitable) {
        $values[$index] = $awaitable->await();
    }

    return $values;
}
