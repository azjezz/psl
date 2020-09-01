<?php

declare(strict_types=1);

namespace Psl\Asio;

use Exception;
use Psl;
use Psl\Str;

/**
 * Represents the result of successful operation.
 *
 * @template    T
 *
 * @implements  IResultOrExceptionWrapper<T>
 */
final class WrappedResult implements IResultOrExceptionWrapper
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
}
