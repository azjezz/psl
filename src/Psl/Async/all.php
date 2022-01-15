<?php

declare(strict_types=1);

namespace Psl\Async;

use Throwable;

/**
 * Awaits all awaitables to complete concurrently.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, Awaitable<Tv>> $awaitables
 *
 * @throws Exception\CompositeException If multiple awaitables failed at once.
 *
 * @return array<Tk, Tv> an array containing the results, preserving the original awaitables order.
 */
function all(iterable $awaitables): array
{
    $values = [];

    // Awaitable::iterate() to throw the first error based on completion order instead of argument order
    foreach (Awaitable::iterate($awaitables) as $index => $awaitable) {
        try {
            $values[$index] = $awaitable->await();
        } catch (Throwable $exception) {
            $errors = [];
            foreach ($awaitables as $original) {
                if ($original === $awaitable) {
                    continue;
                }

                if (!$original->isComplete()) {
                    $original->ignore();
                } else {
                    try {
                        $original->await();
                    } catch (Throwable $error) {
                        $errors[] = $error;
                    }
                }
            }

            if ($errors === []) {
                throw $exception;
            }

            throw new Exception\CompositeException([$exception, ...$errors], 'Multiple exceptions thrown while waiting.');
        }
    }

    $result = [];
    foreach ($awaitables as $k => $_) {
        $result[$k] = $values[$k];
    }

    return $result;
}
