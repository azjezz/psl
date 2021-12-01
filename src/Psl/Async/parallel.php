<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Dict;

/**
 * Run the tasks iterable of functions in parallel, without waiting until the previous function has completed.
 *
 * If one tasks fails, the exception will be thrown immediately, and the result of the callables will be ignored.
 *
 * If multiple tasks failed at once, a {@see Exception\CompositeException} will be thrown.
 *
 * Once the tasks have completed, an array containing the results will be returned preserving the original awaitables order.
 *
 * <code>
 * use Psl\Async;
 *
 * // execute `getOne()` and `getTwo()` functions in parallel.
 *
 * [$one, $two] = Async\parallel([
 *   getOne(...),
 *   getTwo(...),
 * ]);
 * </code>
 *
 * @note    {@see parallel()} is about kicking-off I/O tasks in parallel, not about parallel execution of code.
 *          If your tasks do not use any timers or perform any I/O, they will actually be executed in series.
 *
 * <code>
 * use Psl\Async;
 *
 * // the following runs in series.
 *
 * [$one, $two] = Async\parallel([
 *   fn() => file_get_contents('path/to/file1.txt'),
 *   fn() => file_get_contents('path/to/file2.txt'),
 * ]);
 * </code>
 *
 * @note    Use {@see reflect()} to continue the execution of other tasks when a task fails.
 *
 * <code>
 * use Psl\Async;
 *
 * // execute `getOne()` and `getTwo()` functions in parallel.
 * // if either one of the given tasks fails, the other will continue execution.
 *
 * [$one, $two] = Async\parallel([
 *   Async\reflect(getOne(...)),
 *   Async\reflect(getTwo(...)),
 * ]);
 * </code>
 *
 * @template Tk of array-key
 * @template Tv
 *
 * @param iterable<Tk, (callable(): Tv)> $tasks
 *
 * @return array<Tk, Tv>
 */
function parallel(iterable $tasks): array
{
    $awaitables = Dict\map(
        $tasks,
        /**
         * @param callable(): Tv $callable
         *
         * @return Awaitable<Tv>
         */
        static fn(callable $callable): Awaitable => run($callable),
    );

    return namespace\all($awaitables);
}
