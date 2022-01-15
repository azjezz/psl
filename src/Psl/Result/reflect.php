<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Throwable;

/**
 * Wraps the given operation in another operation that always completes with a {@see Success},
 * or {@see Failure} if the closure throws an {@see Throwable}.
 *
 * @template T
 *
 * @param Closure(): T $task
 *
 * @return Closure(): ResultInterface<T>
 *
 * @see wrap()
 *
 * @pure
 */
function reflect(Closure $task): Closure
{
    return static fn() => wrap($task);
}
