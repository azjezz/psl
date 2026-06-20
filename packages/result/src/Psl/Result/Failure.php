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
final readonly class Failure<T, Te : Throwable> implements ResultInterface<T>
{
    /**
     * @var Te
     */
    private Throwable $throwable;

    /**
     * @param Te $throwable
     *
     * @psalm-mutation-free
     */
    public function __construct(Throwable $throwable)
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
     *
     * @param D $default
     *
     * @return T|D
     */
    #[Override]
    public function unwrapOr<D>(D $default): T|D
    {
        return $default;
    }

    /**
     * Since this is a failed result wrapper, this always returns the `Throwable` thrown during the operation.
     *
     * @return Te - The `Throwable` thrown during the operation.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getThrowable(): Throwable
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
     *
     * @return Ts
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
     *
     * @return ResultInterface<Ts>
     */
    #[Override]
    public function then<Ts>(Closure $success, Closure $failure): ResultInterface<Ts>
    {
        return namespace\wrap(fn(): mixed => $failure($this->throwable));
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(T): Ts $success
     *
     * @return ResultInterface<Ts>
     */
    #[Override]
    public function map<Ts>(Closure $success): ResultInterface<Ts>
    {
        return new Failure($this->throwable);
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(Throwable): Ts $failure
     *
     * @return ResultInterface<T|Ts>
     */
    #[Override]
    public function catch<Ts>(Closure $failure): ResultInterface<T|Ts>
    {
        return namespace\wrap(fn(): mixed => $failure($this->throwable));
    }

    /**
     * {@inheritDoc}
     *
     * @param Closure(): void $always
     *
     * @return ResultInterface<T>
     */
    #[Override]
    public function always(Closure $always): ResultInterface
    {
        return namespace\wrap(function () use ($always): never {
            $always();

            throw $this->throwable;
        });
    }
}
