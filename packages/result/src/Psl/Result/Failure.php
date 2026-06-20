<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Override;
use Throwable;

/**
 * Represents the result of failed operation.
 *
 * @api
 */
final readonly class Failure<out T, out Te: Throwable> implements ResultInterface<T>
{
    private Te $throwable;

    /**
     * @psalm-mutation-free
     */
    public function __construct(Te $throwable)
    {
        $this->throwable = $throwable;
    }

    /**
     * Since this is a failed result wrapper, this always throws the `Throwable` thrown during the operation.
     *
     * @throws Throwable
     */
    #[Override]
    public function getResult(): never
    {
        throw $this->throwable;
    }

    /**
     * Unwrap the Result if it is succeeded or return $default value.
     */
    #[Override]
    public function unwrapOr<D>(D $default): D
    {
        return $default;
    }

    /**
     * Since this is a failed result wrapper, this always returns the `Throwable` thrown during the operation.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getThrowable(): Te
    {
        return $this->throwable;
    }

    /**
     * Since this is a failed result wrapper, this always returns `false`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isSucceeded(): bool
    {
        return false;
    }

    /**
     * Since this is a failed result wrapper, this always returns `true`.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isFailed(): bool
    {
        return true;
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(T): Ts $success
     * @param Closure(Throwable): Ts $failure
     */
    #[Override]
    public function proceed<Ts>(Closure $success, Closure $failure): Ts
    {
        return $failure($this->throwable);
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(T): Ts $success
     * @param Closure(Throwable): Ts $failure
     */
    #[Override]
    public function then<Ts>(Closure $success, Closure $failure): ResultInterface<Ts>
    {
        return namespace\wrap::<Ts>(fn(): mixed => $failure($this->throwable));
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(T): Ts $success
     */
    #[Override]
    public function map<Ts>(Closure $success): Failure<Ts, Te>
    {
        return new Failure::<Ts, Te>($this->throwable);
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(Throwable): Ts $failure
     */
    #[Override]
    public function catch<Ts>(Closure $failure): ResultInterface<Ts>
    {
        return namespace\wrap::<Ts>(fn(): mixed => $failure($this->throwable));
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(): void $always
     */
    #[Override]
    public function always(Closure $always): ResultInterface<T>
    {
        return namespace\wrap::<T>(function () use ($always): never {
            $always();

            throw $this->throwable;
        });
    }
}
