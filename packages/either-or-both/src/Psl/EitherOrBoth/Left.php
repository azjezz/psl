<?php

declare(strict_types=1);

namespace Psl\EitherOrBoth;

use Closure;
use Psl\Comparison;
use Psl\Option;

/**
 * The Left variant of {@see EitherOrBoth}: only a left value is present.
 *
 * @api
 */
final readonly class Left<TLeft = mixed> implements EitherOrBoth<TLeft, never>
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
     * @return true
     *
     * @psalm-mutation-free
     */
    public function isLeft(): bool
    {
        return true;
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
     * @return false
     *
     * @psalm-mutation-free
     */
    public function isBoth(): bool
    {
        return false;
    }

    /**
     * @return true
     *
     * @psalm-mutation-free
     */
    public function hasLeft(): bool
    {
        return true;
    }

    /**
     * @return false
     *
     * @psalm-mutation-free
     */
    public function hasRight(): bool
    {
        return false;
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
     * @throws Exception\MissingRightException Always, since this is a Left.
     *
     * @psalm-mutation-free
     */
    public function getRight(): never
    {
        throw new Exception\MissingRightException('Attempting to get a right value from a left.');
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
     * @return Option\Option<never>
     *
     * @psalm-mutation-free
     */
    public function unwrapRight(): Option\Option
    {
        return Option\none();
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TResult>
     */
    public function map<TResult = mixed>(Closure $closure): Left<TResult>
    {
        return new Left($closure($this->value));
    }

    /**
     * @param (Closure(TLeft): TResult) $closure
     *
     * @param-immediately-invoked-callable $closure
     *
     * @return Left<TResult>
     */
    public function mapLeft<TResult = mixed>(Closure $closure): Left<TResult>
    {
        return new Left($closure($this->value));
    }

    /**
     * @param (Closure(never): TResult) $closure
     *
     * @return Left<TLeft>
     *
     * @psalm-mutation-free
     */
    public function mapRight<TResult = mixed>(Closure $closure): Left<TLeft>
    {
        return $this;
    }

    /**
     * @param (Closure(TLeft): TResultLeft)   $left
     * @param (Closure(never): TResultRight) $right
     *
     * @param-immediately-invoked-callable $left
     *
     * @return Left<TResultLeft>
     */
    public function mapAny<TResultLeft = mixed, TResultRight = mixed>(Closure $left, Closure $right): Left<TResultLeft>
    {
        return new Left($left($this->value));
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
     * @param (Closure(TLeft): TResult)          $left
     * @param (Closure(never): TResult)          $right
     * @param (Closure(TLeft, never): TResult)   $both
     *
     * @param-immediately-invoked-callable $left
     *
     * @return TResult
     */
    public function proceed<TResult = mixed>(Closure $left, Closure $right, Closure $both): TResult
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
     * @psalm-mutation-free
     */
    public function containsLeft(mixed $value): bool
    {
        return $this->value === $value;
    }

    /**
     * @psalm-mutation-free
     */
    public function containsRight(mixed $value): bool
    {
        return false;
    }

    /**
     * @param EitherOrBoth<TLeft, mixed> $other
     */
    public function equals(mixed $other): bool
    {
        return Comparison\equal($this, $other);
    }
}
