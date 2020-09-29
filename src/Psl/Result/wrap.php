<?php

declare(strict_types=1);

namespace Psl\Result;

use Exception;

/**
 * Wrap the given callable result in a `Success`, or `Failure` if the callable throws
 * an `Exception`.
 *
 * @template     T
 *
 * @psalm-param  (callable(): T) $fun
 *
 * @psalm-return ResultInterface<T>
 */
function wrap(callable $fun): ResultInterface
{
    try {
        $result = $fun();
        return new Success($result);
    } catch (Exception $e) {
        return new Failure($e);
    }
}
