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
final readonly class Right<TRight = mixed> implements Either<never, TRight>
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
     * @param T $default
     *
     * @return TRight|T
     *
     * @psalm-mutation-free
     */
    public function getRightOr<T = mixed>(T $default): TRight|T
    {
        return $this->value;
    }

    /**
     * @param T $default
     *
     * @return never|T
     *
     * @psalm-mutation-free
     */
    public function getLeftOr<T = mixed>(T $default): never|T
    {
        return $default;
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @return TRight|TResult
     */
    public function getRightOrElse<TResult = mixed>(Closure $closure): TRight|TResult
    {
        return $this->value;
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return never|TResult
     */
    public function getLeftOrElse<TResult = mixed>(Closure $closure): never|TResult
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
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResult, TResult>
     */
    public function map<TResult = mixed>(Closure $closure): Either<TResult, TResult>
    {
        return new Right($closure($this->value));
    }

    /**
     * @param (Closure(TRight): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<never, TResult>
     */
    public function mapRight<TResult = mixed>(Closure $closure): Either<never, TResult>
    {
        return new Right($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @return Either<TResult, TRight>
     *
     * @psalm-mutation-free
     */
    public function mapLeft<TResult = mixed>(Closure $closure): Either<TResult, TRight>
    {
        return $this;
    }

    /**
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TResultRight>
     */
    public function flatMap<TResultLeft = mixed, TResultRight = mixed>(Closure $closure): Either<TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(TRight): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<never|TResultLeft, TResultRight>
     */
    public function flatMapRight<TResultLeft = mixed, TResultRight = mixed>(Closure $closure): Either<never|TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): Either<TResultLeft, TResultRight>) $closure
     *
     * @return Either<TResultLeft, TRight|TResultRight>
     *
     * @psalm-mutation-free
     */
    public function flatMapLeft<TResultLeft = mixed, TResultRight = mixed>(Closure $closure): Either<TResultLeft, TRight|TResultRight>
    {
        return $this;
    }

    /**
     * @param (Closure(TRight): TResult) $right
     * @param (Closure(never): TResult) $left
     *
     * @param-immediately-invoked-callable $right
     *
     * @return TResult
     */
    public function proceed<TResult = mixed>(Closure $right, Closure $left): TResult
    {
        return $right($this->value);
    }

    /**
     * @param (Closure(TRight): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<never, TRight>
     */
    public function apply(Closure $closure): Either
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @return Either<TRight, never>
     *
     * @psalm-mutation-free
     */
    public function swap(): Either
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
