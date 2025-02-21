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
final readonly class Success implements ResultInterface
{
    /**
     * @var T
     */
    private mixed $value;

    /**
     * @param T $value
     *
     * @psalm-mutation-free
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
    #[\Override]
    public function getResult(): mixed
    {
        return $this->value;
    }

    /**
     * Unwrap the Result if it is succeeded or return $default value.
     *
     * @template D
     *
     * @param D $default
     *
     * @return T
     */
    #[\Override]
    public function unwrapOr(mixed $default): mixed
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function then(Closure $success, Closure $failure): ResultInterface
    {
        return wrap(fn(): mixed => $success($this->value));
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
    #[\Override]
    public function map(Closure $success): ResultInterface
    {
        return wrap(fn(): mixed => $success($this->value));
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
    #[\Override]
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
    #[\Override]
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
