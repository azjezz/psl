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
final readonly class Success<+T> implements ResultInterface<T>
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
    #[Override]
    public function getResult(): mixed
    {
        return $this->value;
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
     *
     * @return Ts
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
     *
     * @return ResultInterface<Ts>
     */
    #[Override]
    public function then<Ts>(Closure $success, Closure $failure): ResultInterface<Ts>
    {
        return namespace\wrap(fn(): mixed => $success($this->value));
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(T): Ts) $success
     *
     * @return ResultInterface<Ts>
     */
    #[Override]
    public function map<Ts>(Closure $success): ResultInterface<Ts>
    {
        return namespace\wrap(fn(): mixed => $success($this->value));
    }

    /**
     * {@inheritDoc}
     *
     * @param (Closure(RootException): Ts) $failure
     *
     * @return ResultInterface<T|Ts>
     */
    #[Override]
    public function catch<Ts>(Closure $failure): ResultInterface<T|Ts>
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
    #[Override]
    public function always(Closure $always): ResultInterface
    {
        return namespace\wrap(
            /**
             * @return T
             */
            function () use ($always): mixed {
                $always();

                return $this->value;
            },
        );
    }
}
