<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Exception as RootException;
use Override;
use Psl;

/**
 * Represents the result of successful operation.
 *
 * @api
 */
final readonly class Success<out T> implements ResultInterface<T>
{
    private T $value;

    /**
     * @psalm-mutation-free
     */
    public function __construct(T $value)
    {
        $this->value = $value;
    }

    /**
     * Since this is a successful result wrapper, this always returns the actual result of the operation.
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getResult(): T
    {
        return $this->value;
    }

    /**
     * Unwrap the Result if it is succeeded or return $default value.
     */
    #[Override]
    public function unwrapOr<D>(D $default): T
    {
        return $this->value;
    }

    /**
     * Since this is a successful result wrapper, this always throws a
     * `Psl\Exception\InvariantViolationException` saying that there was no exception thrown from the operation.
     *
     * @throws Psl\Exception\InvariantViolationException
     *
     * @codeCoverageIgnore
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function getThrowable(): never
    {
        Psl\invariant_violation('No exception thrown from the operation.');
    }

    /**
     * Since this is a successful result wrapper, this always returns `true`.
     *
     * @return true
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isSucceeded(): bool
    {
        return true;
    }

    /**
     * Since this is a successful result wrapper, this always returns `false`.
     *
     * @return false
     *
     * @psalm-mutation-free
     */
    #[Override]
    public function isFailed(): bool
    {
        return false;
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(T): Ts) $success
     * @param (Closure(RootException): Ts) $failure
     */
    #[Override]
    public function proceed<Ts>(Closure $success, Closure $failure): Ts
    {
        return $success($this->value);
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(T): Ts) $success
     * @param (Closure(RootException): Ts) $failure
     */
    #[Override]
    public function then<Ts>(Closure $success, Closure $failure): ResultInterface<Ts>
    {
        return namespace\wrap::<Ts>(fn(): mixed => $success($this->value));
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(T): Ts) $success
     */
    #[Override]
    public function map<Ts>(Closure $success): ResultInterface<Ts>
    {
        return namespace\wrap::<Ts>(fn(): mixed => $success($this->value));
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(RootException): Ts) $failure
     */
    #[Override]
    public function catch<Ts>(Closure $failure): Success<T>
    {
        return new Success::<T>($this->value);
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(): void) $always
     */
    #[Override]
    public function always(Closure $always): ResultInterface<T>
    {
        return namespace\wrap::<T>(
            function () use ($always): T {
                $always();

                return $this->value;
            },
        );
    }
}
