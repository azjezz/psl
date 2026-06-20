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
final readonly class Left<TLeft> implements Either<TLeft, never>
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
     * @param T $default
     *
     * @return never|T
     *
     * @psalm-mutation-free
     */
    public function getRightOr<T>(T $default): never|T
    {
        return $default;
    }

    /**
     * @param T $default
     *
     * @return TLeft|T
     *
     * @psalm-mutation-free
     */
    public function getLeftOr<T>(T $default): TLeft|T
    {
        return $this->value;
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return never|TResult
     */
    public function getRightOrElse<TResult>(Closure $closure): never|TResult
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @return TLeft|TResult
     */
    public function getLeftOrElse<TResult>(Closure $closure): TLeft|TResult
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
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResult, TResult>
     */
    public function map<TResult>(Closure $closure): Either<TResult, TResult>
    {
        return new Left($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @return Either<TLeft, TResult>
     *
     * @psalm-mutation-free
     */
    public function mapRight<TResult>(Closure $closure): Either<TLeft, TResult>
    {
        return $this;
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResult, never>
     */
    public function mapLeft<TResult>(Closure $closure): Either<TResult, never>
    {
        return new Left($closure($this->value));
    }

    /**
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, TResultRight>
     */
    public function flatMap<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): Either<TResultLeft, TResultRight>) $closure
     *
     * @return Either<TLeft|TResultLeft, TResultRight>
     *
     * @psalm-mutation-free
     */
    public function flatMapRight<TResultLeft, TResultRight>(Closure $closure): Either<TLeft|TResultLeft, TResultRight>
    {
        return $this;
    }

    /**
     * @param (Closure(TLeft): Either<TResultLeft, TResultRight>) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TResultLeft, never|TResultRight>
     */
    public function flatMapLeft<TResultLeft, TResultRight>(Closure $closure): Either<TResultLeft, never|TResultRight>
    {
        return $closure($this->value);
    }

    /**
     * @param (Closure(never): TResult) $right
     * @param (Closure(TLeft): TResult) $left
     *
     * @param-immediately-invoked-callable $left
     *
     * @return TResult
     */
    public function proceed<TResult>(Closure $right, Closure $left): TResult
    {
        return $left($this->value);
    }

    /**
     * @param (Closure(TLeft): mixed) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Either<TLeft, never>
     */
    public function apply(Closure $closure): Either
    {
        $closure($this->value);

        return $this;
    }

    /**
     * @return Either<never, TLeft>
     *
     * @psalm-mutation-free
     */
    public function swap(): Either
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
