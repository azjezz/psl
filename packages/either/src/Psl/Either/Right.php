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
 * @api
 */
final readonly class Right<out TRight> implements Either<never, TRight>
{
    private TRight $value;

    /**
     * @psalm-mutation-free
     */
    public function __construct(TRight $value)
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
     * @psalm-mutation-free
     */
    public function getRight(): TRight
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
     * @psalm-mutation-free
     */
    public function getRightOr<T>(T $default): TRight
    {
        return $this->value;
    }

    /**
     * @psalm-mutation-free
     */
    public function getLeftOr<T>(T $default): T
    {
        return $default;
    }

    public function getRightOrElse(Closure $closure): TRight
    {
        return $this->value;
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function getLeftOrElse<TResult>(Closure $closure): TResult
    {
        return $closure($this->value);
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<TRight>
    {
        return Option\some::<TRight>($this->value);
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<never>
    {
        return Option\none();
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): Right<TResult>
    {
        return new Right::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapRight<TResult>(Closure $closure): Right<TResult>
    {
        return new Right::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @psalm-mutation-free
     */
    public function mapLeft<TResult>(Closure $closure): Right<TRight>
    {
        return $this;
    }

    /**
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMap<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMapRight<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): Either<TResultLeft, TResultRight>) $closure
     *
     * @psalm-mutation-free
     */
    public function flatMapLeft<TResultLeft, TResultRight>(Closure $closure): Right<TRight>
    {
        return $this;
    }

    /**
     * @param (Closure(TRight): TResult) $right
     * @param (Closure(never): TResult) $left
     *
     * @param-immediately-invoked-callable $right
     */
    public function proceed<TResult>(Closure $right, Closure $left): TResult
    {
        return $right($this->value);
    }

    /**
     * @param (Closure(TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): Right<TRight>
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @psalm-mutation-free
     */
    public function swap(): Left<TRight>
    {
        return new Left::<TRight>($this->value);
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
     * @throws Exception\LeftException
     */
    public function compare(Either<mixed, mixed> $other): Comparison\Order
    {
        if ($other instanceof Left) {
            return Comparison\Order::Greater;
        }

        return Comparison\compare::<TRight>($this->value, $other->getRight());
    }

    public function equals(Either<mixed, mixed> $other): bool
    {
        return Comparison\equal::<Either<mixed, mixed>>($this, $other);
    }
}
