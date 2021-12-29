<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

/**
 * Run the functions in the tasks' iterable concurrently, without waiting until the previous function has completed.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, (Closure(): Tv)> $tasks
 *
 * @throws Exception\CompositeException If multiple functions failed at once.
 *
 * @return array<Tk, Tv> an array containing the results, preserving the original functions order.
 */
function concurrently(iterable $tasks): array
{
    $awaitables = [];
    foreach ($tasks as $k => $task) {
        $awaitables[$k] = run($task);
    }

    return namespace\all($awaitables);
}
