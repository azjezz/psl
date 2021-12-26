<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Psl\Dict;

/**
 * Run the functions in the tasks' iterable in series, each one running once the previous function has completed.
 *
 * If any functions in the series throws, no more functions are run, and the exception is immediately thrown.
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, (Closure(): Tv)> $tasks
 *
 * @return array<Tk, Tv>
 */
function series(iterable $tasks): array
{
    return Dict\map(
        $tasks,
        /**
         * @param (Closure(): Tv) $closure
         *
         * @return Tv
         */
        static fn(Closure $closure): mixed => run($closure)->await(),
    );
}
