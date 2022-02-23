<?php

declare(strict_types=1);

namespace Psl\Result;

use Closure;
use Exception as RootException;
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
     * @var T
     *
     * @readonly
     */
    private mixed $value;

    /**
     * @param T $value
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * Since this is a successful result wrapper, this always returns the actual result of the operation.
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getResult(): mixed
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
    public function isFailed(): bool
    {
        return false;
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
        return $success($this->value);
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
        return wrap(fn () => $success($this->value));
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param (Closure(T): Ts) $success
     *
     * @return ResultInterface<Ts>
     */
    public function map(Closure $success): ResultInterface
    {
        return wrap(fn () => $success($this->value));
    }

    /**
     * {@inheritDoc}
     *
     * @template Ts
     *
     * @param (Closure(RootException): Ts) $failure
     *
     * @return Success<T>
     */
    public function catch(Closure $failure): Success
    {
        return new Success($this->value);
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
        return wrap(
            /**
             * @return T
             */
            function () use ($always): mixed {
                $always();

                /** @var T */
                return $this->value;
            },
        );
    }
}
