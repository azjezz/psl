<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Psl\Dict;

/**
 * Run the iterable of functions in parallel, without waiting until the previous function has completed.
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
function parallel(iterable $tasks): array
{
    $awaitables = Dict\map(
        $tasks,
        /**
         * @param (Closure(): Tv) $closure
         *
         * @return Awaitable<Tv>
         */
        static fn(Closure $closure): Awaitable => run($closure),
    );

    return namespace\all($awaitables);
}
