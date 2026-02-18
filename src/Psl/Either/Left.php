<?php

declare(strict_types=1);

namespace Psl\Either;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * Represents the Left side of an {@see Either}.
 *
 * By convention, Left represents the failure/error case.
 *
 * @template TLeft
 *
 * @implements Either<TLeft, never>
 */
final readonly class Left implements Either
{
    /**
     * @var TLeft
     */
    private mixed $value;

    /**
     * @param TLeft $value
     *
     * @psalm-mutation-free
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @return false
     *
     * @psalm-mutation-free
     */
    public function isRight(): bool
    {
        return false;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool
    {
        return true;
    }

    /**
     * @throws Exception\LeftException Always, since this is a Left.
     *
     * @psalm-mutation-free
     */
    public function getRight(): never
    {
        throw new Exception\LeftException('Attempting to get a right value from a left either.');
    }

    /**
     * @return TLeft
     *
     * @psalm-mutation-free
     */
    public function getLeft(): mixed
    {
        return $this->value;
    }

    /**
     * @template T
     *
     * @param T $default
     *
     * @return T
     *
     * @psalm-mutation-free
     */
    public function getRightOr(mixed $default): mixed
    {
        return $default;
    }

    /**
     * @template T
     *
     * @param T $default
     *
     * @return TLeft
     *
     * @psalm-mutation-free
     */
    public function getLeftOr(mixed $default): mixed
    {
        return $this->value;
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return TResult
     */
    public function getRightOrElse(Closure $closure): mixed
    {
        return $closure($this->value);
    }

    /**
     * @return TLeft
     */
    public function getLeftOrElse(Closure $closure): mixed
    {
        return $this->value;
    }

    /**
     * @return Option\Option<never>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option
    {
        return Option\none();
    }

    /**
     * @return Option\Option<TLeft>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option
    {
        return Option\some($this->value);
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TResult>
     */
    public function map(Closure $closure): Left
    {
        return new Left($closure($this->value));
    }

    /**
     * @template TResult
     *
     * @param (Closure(never): TResult) $closure
     *
     * @return Left<TLeft>
     *
     * @psalm-mutation-free
     */
    public function mapRight(Closure $closure): Left
    {
        return $this;
    }

    /**
     * @template TResult
     *
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TResult>
     */
    public function mapLeft(Closure $closure): Left
    {
        return new Left($closure($this->value));
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TResultRight>
     */
    public function flatMap(Closure $closure): Either
    {
        return $closure($this->value);
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(never): Either<TResultLeft, TResultRight>) $closure
     *
     * @return Left<TLeft>
     *
     * @psalm-mutation-free
     */
    public function flatMapRight(Closure $closure): Left
    {
        return $this;
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TResultRight>
     */
    public function flatMapLeft(Closure $closure): Either
    {
        return $closure($this->value);
    }

    /**
     * @template TResult
     *
     * @param (Closure(never): TResult) $right
     * @param (Closure(TLeft): TResult) $left
     *
     * @param-immediately-invoked-callable $left
     *
     * @return TResult
     */
    public function proceed(Closure $right, Closure $left): mixed
    {
        return $left($this->value);
    }

    /**
     * @param (Closure(TLeft): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TLeft>
     */
    public function apply(Closure $closure): Left
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @return Right<TLeft>
     *
     * @psalm-mutation-free
     */
    public function swap(): Right
    {
        return new Right($this->value);
    }

    /**
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool
    {
        return false;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @param Either<TLeft, mixed> $other
     *
     * @throws Exception\RightException
     */
    public function compare(mixed $other): Comparison\Order
    {
        if ($other instanceof Right) {
            return Comparison\Order::Less;
        }

        return Comparison\compare($this->value, $other->getLeft());
    }

    /**
     * @param Either<TLeft, mixed> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}
