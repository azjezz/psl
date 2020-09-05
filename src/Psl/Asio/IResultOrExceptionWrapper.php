<?php

declare(strict_types=1);

namespace Psl\Asio;

use Exception;
use Psl;

/**
 * Represents a result of operation that either has a successful result or the exception object if
 * that operation failed.
 *
 * This is an interface. You get generally `IResultOrExceptionWrapper<T>` by calling `wrap<T>()`, passing in
 * the `callable(): T`, and a `WrappedResult<T>` or `WrappedException<Te>` is returned.
 *
 * @template T
 */
interface IResultOrExceptionWrapper
{
    /**
     * Return the result of the operation, or throw underlying exception.
     *
     * - if the operation succeeded: return its result.
     * - if the operation failed: throw the exception inciting failure.
     *
     * @psalm-return T - The result of the operation upon success
     */
    public function getResult();

    /**
     * Return the underlying exception, or fail with a invariant violation exception exception.
     *
     * - if the operation succeeded: fails with a invariant violation exception.
     * - if the operation failed: returns the exception indicating failure.
     *
     * @throws Psl\Exception\InvariantViolationException - When the operation succeeded
     */
    public function getException(): Exception;

    /**
     * Indicates whether the operation associated with this wrapper existed normally.
     *
     * if `isSucceeded()` returns `true`, `isFailed()` returns false.
     *
     * @return bool - `true` if the operation succeeded; `false` otherwise
     */
    public function isSucceeded(): bool;

    /**
     * Indicates whether the operation associated with this wrapper exited abnormally via an exception of some sort.
     *
     * if `isFailed()` returns `true`, `isSucceeded()` returns false.
     *
     * @return bool - `true` if the operation failed; `false` otherwise
     */
    public function isFailed(): bool;
}
