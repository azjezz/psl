<?php

declare(strict_types=1);

namespace Psl\Result;

use Exception;
use Psl;

/**
 * Represents a result of operation that either has a successful result or the exception object if
 * that operation failed.
 *
 * This is an interface. You get generally `ResultInterface<T>` by calling `tryResultFrom<T>()`, passing in
 * the `callable(): T`, and a `Success<T>` or `Failure<Te>` is returned.
 *
 * @template T
 */
interface ResultInterface
{
    /**
     * Return the result of the operation, or throw underlying exception.
     *
     * - if the operation succeeded: return its result.
     * - if the operation failed: throw the exception inciting failure.
     *
     * @return T - The result of the operation upon success
     *
     * @psalm-mutation-free
     */
    public function getResult();

    /**
     * Return the underlying exception, or fail with a invariant violation exception exception.
     *
     * - if the operation succeeded: fails with a invariant violation exception.
     * - if the operation failed: returns the exception indicating failure.
     *
     * @throws Psl\Exception\InvariantViolationException - When the operation succeeded
     *
     * @psalm-mutation-free
     */
    public function getException(): Exception;

    /**
     * Indicates whether the operation associated with this wrapper existed normally.
     *
     * if `isSucceeded()` returns `true`, `isFailed()` returns false.
     *
     * @return bool - `true` if the operation succeeded; `false` otherwise
     *
     * @psalm-mutation-free
     */
    public function isSucceeded(): bool;

    /**
     * Indicates whether the operation associated with this wrapper exited abnormally via an exception of some sort.
     *
     * if `isFailed()` returns `true`, `isSucceeded()` returns false.
     *
     * @return bool - `true` if the operation failed; `false` otherwise
     *
     * @psalm-mutation-free
     */
    public function isFailed(): bool;

    /**
     * Unwrapping and transforming a result can be done by using the proceed method.
     * The implementation will either run the `$on_success` or `$on_failure` callback.
     * The callback will receive the result or Exception as an argument,
     * so that you can transform it to anything you want.
     *
     * @template R
     *
     * @param callable(T): R $on_success
     * @param callable(Exception): R $on_failure
     *
     * @return R
     */
    public function proceed(callable $on_success, callable $on_failure);

    /**
     * The method can be used to transform a result into another result.
     * The implementation will either run the `$on_success` or `$on_failure` callback.
     * The callback will receive the result value or Exception as an argument,
     * so that you can transform use it to build a new result.
     *
     * This method is compatible with the `PromiseInterface::then()` function from `reactphp/promise`.
     * You can use it in an async context as long as the package you are using is compatible with reactphp promises.
     *
     * @link https://github.com/reactphp/promise#promiseinterfacethen
     *
     * @template R
     *
     * @param callable(T): R $on_success
     * @param callable(Exception): R $on_failure
     *
     * @return ResultInterface<R>
     */
    public function then(callable $on_success, callable $on_failure): ResultInterface;
}
