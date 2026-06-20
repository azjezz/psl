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
 * @api
 */
final readonly class Left<out TLeft> implements Either<TLeft, never>
{
    private TLeft $value;

    /**
     * @psalm-mutation-free
     */
    public function __construct(TLeft $value)
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
     * @psalm-mutation-free
     */
    public function getLeft(): TLeft
    {
        return $this->value;
    }

    /**
     * @psalm-mutation-free
     */
    public function getRightOr<T>(T $default): T
    {
        return $default;
    }

    /**
     * @psalm-mutation-free
     */
    public function getLeftOr<T>(T $default): TLeft
    {
        return $this->value;
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function getRightOrElse<TResult>(Closure $closure): TResult
    {
        return $closure($this->value);
    }

    public function getLeftOrElse(Closure $closure): TLeft
    {
        return $this->value;
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option<never>
    {
        return Option\none();
    }

    /**
     * @psalm-mutation-free
     */
    public function unwrapLeft(): Option\Option<TLeft>
    {
        return Option\some::<TLeft>($this->value);
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function map<TResult>(Closure $closure): Left<TResult>
    {
        return new Left::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @psalm-mutation-free
     */
    public function mapRight<TResult>(Closure $closure): Left<TLeft>
    {
        return $this;
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function mapLeft<TResult>(Closure $closure): Left<TResult>
    {
        return new Left::<TResult>($closure($this->value));
    }

    /**
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMap<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): Either<TResultLeft, TResultRight>) $closure
     *
     * @psalm-mutation-free
     */
    public function flatMapRight<TResultLeft, TResultRight>(Closure $closure): Left<TLeft>
    {
        return $this;
    }

    /**
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function flatMapLeft<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): TResult) $right
     * @param (Closure(TLeft): TResult) $left
     *
     * @param-immediately-invoked-callable $left
     */
    public function proceed<TResult>(Closure $right, Closure $left): TResult
    {
        return $left($this->value);
    }

    /**
     * @param (Closure(TLeft): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     */
    public function apply(Closure $closure): Left<TLeft>
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @psalm-mutation-free
     */
    public function swap(): Right<TLeft>
    {
        return new Right::<TLeft>($this->value);
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
     * @throws Exception\RightException
     */
    public function compare(Either<mixed, mixed> $other): Comparison\Order
    {
        if ($other instanceof Right) {
            return Comparison\Order::Less;
        }

        return Comparison\compare::<TLeft>($this->value, $other->getLeft());
    }

    public function equals(Either<mixed, mixed> $other): bool
    {
        return Comparison\equal::<Either<mixed, mixed>>($this, $other);
    }
}
