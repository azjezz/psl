<?php

declare(strict_types=1);

namespace Psl\Async;

use Closure;
use Exception;
use Psl\Result;

/**
 * Wraps the given task in another task that always completes with a {@see Result\Success},
 * or {@see Result\Failure} if the callable throws an {@see Exception}.
 *
 * @template T
 *
 * @param (callable(): T) $task
 *
 * @return (Closure(): Result\ResultInterface<T>)
 *
 * @see Result\wrap()
 *
 * @pure
 */
function reflect(callable $task): Closure
{
    return static fn() => Result\wrap($task);
}
