<?php

declare(strict_types=1);

namespace Psl\Result;

use Exception;
use Psl;

/**
 * Represents the result of successful operation.
 *
 * @template    T
 *
 * @implements  ResultInterface<T>
 */
final class Success implements ResultInterface
{
    /**
     * @psalm-var T
     */
    private $value;

    /**
     * @psalm-param T $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Since this is a successful result wrapper, this always returns the actual result of the operation.
     *
     * @psalm-return T
     */
    public function getResult()
    {
        return $this->value;
    }

    /**
     * Since this is a successful result wrapper, this always throws a
     * `Psl\Exception\InvariantViolationException` saying that there was no exception thrown from the operation.
     *
     * @throws Psl\Exception\InvariantViolationException
     *
     * @psalm-return no-return
     *
     * @codeCoverageIgnore
     */
    public function getException(): Exception
    {
        Psl\invariant_violation('No exception thrown from the operation.');
    }

    /**
     * Since this is a successful result wrapper, this always returns `true`.
     *
     * @return true
     */
    public function isSucceeded(): bool
    {
        return true;
    }

    /**
     * Since this is a successful result wrapper, this always returns `false`.
     *
     * @return false
     */
    public function isFailed(): bool
    {
        return false;
    }

    /**
     * Unwrapping and transforming a result can be done by using the proceed method.
     * Since this is a success result wrapper, the `$on_success` callback will be triggered.
     * The callback will receive the result value as an argument, so that you can transform it to anything you want.
     */
    public function proceed(callable $on_success, callable $on_failure)
    {
        return $on_success($this->value);
    }

    /**
     * The method can be used to transform a result into another result.
     * Since this is a success result wrapper, the `$on_success` callback will be triggered.
     * The callback will receive the result value as an argument, so that you can use it to create a new result.
     */
    public function then(callable $on_success, callable $on_failure): ResultInterface
    {
        return wrap(fn () => $on_success($this->value));
    }
}
