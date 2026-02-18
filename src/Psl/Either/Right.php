<?php

declare(strict_types=1);

namespace Psl\Either;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * Represents the Right side of an {@see Either}.
 *
 * By convention, Right represents the success case.
 *
 * @template TRight
 *
 * @implements Either<never, TRight>
 */
final readonly class Right implements Either
{
    /**
     * @var TRight
     */
    private mixed $value;

    /**
     * @param TRight $value
     *
     * @psalm-mutation-free
     */
    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function isRight(): bool
    {
        return true;
    }

    /**
     * @return false
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool
    {
        return false;
    }

    /**
     * @return TRight
     *
     * @psalm-mutation-free
     */
    public function getRight(): mixed
    {
        return $this->value;
    }

    /**
     * @throws Exception\RightException Always, since this is a Right.
     *
     * @psalm-mutation-free
     */
    public function getLeft(): never
    {
        throw new Exception\RightException('Attempting to get a left value from a right either.');
    }

    /**
     * @template T
     *
     * @param T $default
     *
     * @return TRight
     *
     * @psalm-mutation-free
     */
    public function getRightOr(mixed $default): mixed
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
    public function getLeftOr(mixed $default): mixed
    {
        return $default;
    }

    /**
     * @return TRight
     */
    public function getRightOrElse(Closure $closure): mixed
    {
        return $this->value;
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return TResult
     */
    public function getLeftOrElse(Closure $closure): mixed
    {
        return $closure($this->value);
    }

    /**
     * @return Option\Option<TRight>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option
    {
        return Option\some($this->value);
    }

    /**
     * @return Option\Option<never>
     *
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option
    {
        return Option\none();
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Right<TResult>
     */
    public function map(Closure $closure): Right
    {
        return new Right($closure($this->value));
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Right<TResult>
     */
    public function mapRight(Closure $closure): Right
    {
        return new Right($closure($this->value));
    }

    /**
     * @template TResult
     *
     * @param (Closure(never): TResult) $closure
     *
     * @return Right<TRight>
     *
     * @psalm-mutation-free
     */
    public function mapLeft(Closure $closure): Right
    {
        return $this;
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
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
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TResultRight>
     */
    public function flatMapRight(Closure $closure): Either
    {
        return $closure($this->value);
    }

    /**
     * @template TResultLeft
     * @template TResultRight
     *
     * @param (Closure(never): Either<TResultLeft, TResultRight>) $closure
     *
     * @return Right<TRight>
     *
     * @psalm-mutation-free
     */
    public function flatMapLeft(Closure $closure): Right
    {
        return $this;
    }

    /**
     * @template TResult
     *
     * @param (Closure(TRight): TResult) $right
     * @param (Closure(never): TResult) $left
     *
     * @param-immediately-invoked-callable $right
     *
     * @return TResult
     */
    public function proceed(Closure $right, Closure $left): mixed
    {
        return $right($this->value);
    }

    /**
     * @param (Closure(TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Right<TRight>
     */
    public function apply(Closure $closure): Right
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @return Left<TRight>
     *
     * @psalm-mutation-free
     */
    public function swap(): Left
    {
        return new Left($this->value);
    }

    /**
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool
    {
        return false;
    }

    /**
     * @param Either<mixed, TRight> $other
     *
     * @throws Exception\LeftException
     */
    public function compare(mixed $other): Comparison\Order
    {
        if ($other instanceof Left) {
            return Comparison\Order::Greater;
        }

        return Comparison\compare($this->value, $other->getRight());
    }

    /**
     * @param Either<mixed, TRight> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}
