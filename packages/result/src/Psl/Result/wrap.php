<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Throwable;

/**
 * Wrap the given closure result in a `Success`, or `Failure` if the closure throws
 * an `Throwable`.
 *
 * @param (Closure(): T) $closure
 *
 * @return ResultInterface<T>
 *
 * @api
 */
function wrap<T = mixed>(Closure $closure): ResultInterface<T>
{
    try {
        return new Success($closure());
    } catch (Throwable $e) {
        return new Failure($e);
    }
}
