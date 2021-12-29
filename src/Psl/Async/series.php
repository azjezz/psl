<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;

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
    $result = [];
    foreach ($tasks as $key => $task) {
        $result[$key] = $task();
    }

    return $result;
}
