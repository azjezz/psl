<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Throwable;

/**
 * Represents the result of failed operation.
 *
 * @template    T
 * @template    Te of Throwable
 *
 * @implements  ResultInterface<T>
 */
final class Failure implements ResultInterface
{
    /**
     * @param Te $throwable
     */
    public function __construct(
        private readonly Throwable $throwable
    ) {
    }

    /**
     * Since this is a failed result wrapper, this always throws the `Throwable` thrown during the operation.
     *
     * @throws Throwable
     *
     * @psalm-mutation-free
     */
    public function getResult(): void
    {
        throw $this->throwable;
    }

    /**
     * Since this is a failed result wrapper, this always returns the `Throwable` thrown during the operation.
     *
     * @return Te - The `Throwable` thrown during the operation.
     *
     * @psalm-mutation-free
     */
    public function getThrowable(): Throwable
    {
        return $this->throwable;
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
     * @param Closure(T): Ts $success
     * @param Closure(Throwable): Ts $failure
     *
     * @return Ts
     */
    public function proceed(Closure $success, Closure $failure): mixed
    {
        return $failure($this->throwable);
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param Closure(T): Ts $success
     * @param Closure(Throwable): Ts $failure
     *
     * @return ResultInterface<Ts>
     */
    public function then(Closure $success, Closure $failure): ResultInterface
    {
        return wrap(fn () => $failure($this->throwable));
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param Closure(T): Ts $success
     *
     * @return Failure<Ts, Te>
     */
    public function map(Closure $success): Failure
    {
        return new Failure($this->throwable);
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param Closure(Throwable): Ts $failure
     *
     * @return ResultInterface<Ts>
     */
    public function catch(Closure $failure): ResultInterface
    {
        return wrap(fn() => $failure($this->throwable));
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(): void $always
     *
     * @return ResultInterface<T>
     */
    public function always(Closure $always): ResultInterface
    {
        return wrap(function () use ($always): never {
            $always();

            throw $this->throwable;
        });
    }
}
