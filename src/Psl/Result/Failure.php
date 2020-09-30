<?php

declare(strict_types=1);

namespace Psl\Result;

use Exception;
use Psl;

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
     * @psalm-var Te
     */
    private Exception $exception;

    /**
     * @psalm-param Te $exception
     */
    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
    }

    /**
     * Since this is a failed result wrapper, this always throws the exception thrown during the operation.
     *
     * @throws Exception
     */
    public function getResult(): void
    {
        throw $this->exception;
    }

    /**
     * Since this is a failed result wrapper, this always returns the exception thrown during the operation.
     *
     * @psalm-return Te - The exception thrown during the operation.
     */
    public function getException(): Exception
    {
        return $this->exception;
    }

    /**
     * Since this is a failed result wrapper, this always returns `false`.
     */
    public function isSucceeded(): bool
    {
        return false;
    }

    /**
     * Since this is a failed result wrapper, this always returns `true`.
     */
    public function isFailed(): bool
    {
        return true;
    }
}
