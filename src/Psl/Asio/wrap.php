<?php

declare(strict_types=1);

namespace Psl\Asio;

/**
 * Wrap the given callable result in a `WrappedResult`, or `WrappedException` if the callable throws
 * an `Exception`.
 *
 * @template     T
 *
 * @psalm-param  (callable(): T) $fun
 *
 * @psalm-return IResultOrExceptionWrapper<T>
 */
function wrap(callable $fun): IResultOrExceptionWrapper
{
    try {
        $result = $fun();
        return new WrappedResult($result);
    } catch (\Exception $e) {
        return new WrappedException($e);
    }
}
