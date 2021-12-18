<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Exception;

/**
 * Wrap the given callable result in a `Success`, or `Failure` if the closure throws
 * an `Exception`.
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
    } catch (Exception $e) {
        return new Failure($e);
    }
}
