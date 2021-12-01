<?php

declare(strict_types=1);

namespace Psl\Async;

use Psl\Result;

/**
 * Wraps the async function in an awaitable that completes with a {@see Result\Success},
 * or {@see Result\Failure} if the task throws an {@see Exception}.
 *
 * @template T
 *
 * @param (callable(): T) $task
 *
 * @return Result\ResultInterface<T>
 *
 * @see reflect()
 */
function wrap(callable $task): Result\ResultInterface
{
    return run(reflect($task))->await();
}
