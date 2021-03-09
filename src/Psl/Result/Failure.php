<?php

declare(strict_types=1);

namespace Psl\Result;

use Exception;

/**
 * Represents the result of failed operation.
 *
 * @template    T
 * @template    Te of Exception
 *
 * @implements  ResultInterface<T>
 */
final class Failure implements ResultInterface
{
    /**
     * @var Te
     *
     * @readonly
     */
    private Exception $exception;

    /**
     * @param Te $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Since this is a failed result wrapper, this always throws the exception thrown during the operation.
     *
     * @throws Exception
     *
     * @psalm-mutation-free
     */
    public function getResult(): void
    {
        throw $this->exception;
    }

    /**
     * Since this is a failed result wrapper, this always returns the exception thrown during the operation.
     *
     * @return Te - The exception thrown during the operation.
     *
     * @psalm-mutation-free
     */
    public function getException(): Exception
    {
        return $this->exception;
    }

    /**
     * Since this is a failed result wrapper, this always returns `false`.
     *
     * @psalm-mutation-free
     */
    public function isSucceeded(): bool
    {
        return false;
    }

    /**
     * Since this is a failed result wrapper, this always returns `true`.
     *
     * @psalm-mutation-free
     */
    public function isFailed(): bool
    {
        return true;
    }

    /**
     * Unwrapping and transforming a result can be done by using the proceed method.
     * Since this is a failed result wrapper, the `$on_failure` callback will be triggered.
     * The callback will receive the Exception as an argument, so that you can transform it to anything you want.
     */
    public function proceed(callable $on_success, callable $on_failure)
    {
        return $on_failure($this->exception);
    }

    /**
     * The method can be used to transform a result into another result.
     * Since this is a failure result wrapper, the `$on_failure` callback will be triggered.
     * The callback will receive the Exception as an argument,
     * so that you can use it to create a success result or rethrow the Exception.
     */
    public function then(callable $on_success, callable $on_failure): ResultInterface
    {
        return wrap(fn () => $on_failure($this->exception));
    }
}
