<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Throwable;

/**
 * Wrap the given closure result in a `Success`, or `Failure` if the closure throws
 * an `Throwable`.
 *
 * @template     T
 *
 * @param (Closure(): (T|ResultInterface<T>)) $closure
 *
 * @return ResultInterface<T>
 */
function wrap(Closure $closure): ResultInterface
{
    try {
        $result = $closure();
        return $result instanceof ResultInterface ? $result : new Success($result);
    } catch (Throwable $e) {
        return new Failure($e);
    }
}
