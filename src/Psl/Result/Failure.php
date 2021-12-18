<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Exception as RootException;

/**
 * Represents the result of failed operation.
 *
 * @template    T
 * @template    Te of RootException
 *
 * @implements  ResultInterface<T>
 */
final class Failure implements ResultInterface
{
    /**
     * @param Te $exception
     */
    public function __construct(
        private readonly RootException $exception
    ) {
    }

    /**
     * Since this is a failed result wrapper, this always throws the exception thrown during the operation.
     *
     * @throws RootException
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
    public function getException(): RootException
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
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     * @param (Closure(RootException): Ts) $failure
     *
     * @return Ts
     */
    public function proceed(Closure $success, Closure $failure): mixed
    {
        return $failure($this->exception);
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     * @param (Closure(RootException): Ts) $failure
     *
     * @return ResultInterface<Ts>
     */
    public function then(Closure $success, Closure $failure): ResultInterface
    {
        return wrap(fn () => $failure($this->exception));
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     *
     * @return Failure<Ts, Te>
     */
    public function map(Closure $success): Failure
    {
        return new Failure($this->exception);
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param (Closure(RootException): Ts) $failure
     *
     * @return ResultInterface<Ts>
     */
    public function catch(Closure $failure): ResultInterface
    {
        return wrap(fn() => $failure($this->exception));
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(): void) $always
     *
     * @return ResultInterface<T>
     */
    public function always(Closure $always): ResultInterface
    {
        return wrap(function () use ($always) {
            $always();

            throw $this->exception;
        });
    }
}
