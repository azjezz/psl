<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Exception as RootException;
use Psl;

/**
 * Represents a result of operation that either has a successful result or the exception object if
 * that operation failed.
 *
 * This is an interface. You get generally `ResultInterface<T>` by calling `wrap<T>()`, passing in
 * the `(Closure(): T)`, and a `Success<T>` or `Failure<Te>` is returned.
 *
 * @template T
 *
 * @extends Psl\Promise\PromiseInterface<T>
 */
interface ResultInterface extends Psl\Promise\PromiseInterface
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
    public function getException(): RootException;

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
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     * @param (Closure(RootException): Ts) $failure
     *
     * @return Ts
     */
    public function proceed(Closure $success, Closure $failure): mixed;
}
