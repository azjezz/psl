<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Throwable;

/**
 * Wraps the given operation in another operation that always completes with a {@see Success},
 * or {@see Failure} if the closure throws an {@see Throwable}.
 *
 * @param Closure(): T $task
 *
 * @return Closure(): ResultInterface<T>
 *
 * @see wrap()
 *
 * @pure
 *
 * @api
 */
function reflect<T = mixed>(Closure $task): Closure
{
    return static fn(): ResultInterface => namespace\wrap($task);
}
