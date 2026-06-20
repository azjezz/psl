<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

/**
 * Run the functions in the tasks' iterable concurrently, without waiting until the previous function has completed.
 *
 * @param iterable<Tk, (Closure(): Tv)> $tasks
 *
 * @throws Exception\CompositeException If multiple functions failed at once.
 *
 * @return array<Tk, Tv> an array containing the results, preserving the original functions order.
 *
 * @api
 */
function concurrently<Tk: string|int, Tv>(iterable $tasks): array
{
    $awaitables = [];
    foreach ($tasks as $k => $task) {
        $awaitables[$k] = namespace\run::<Tv>($task);
    }

    return namespace\all::<Tk, Tv>($awaitables);
}
